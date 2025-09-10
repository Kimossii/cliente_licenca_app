<?php
namespace App\Services;
use Carbon\Carbon;
use App\Helpers\LicenseHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use App\Models\License;
class LicenseService
{
    public function uploadPublicKey(UploadedFile $file, bool $overwrite = false): array
    {
        $path = $this->getStorageKeys(); // ou crie getStorageKeys() no serviço

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        } elseif (File::exists($path . '/public.pem') && !$overwrite) {
            return ['success' => false, 'message' => 'Chave pública já existe. Marque para substituir.'];
        }

        $file->move($path, 'public.pem');

        return ['success' => true, 'message' => 'Chave pública enviada com sucesso!'];
    }



    public function getStorageKeys(): string
    {
        return storage_path('keys');
    }

    private function extraSecret()
    {

        return LicenseHelper::getSegredoExtra();
    }
    public function getClientStoragePathKeyPublic()
    {
        return config('license.client.storage_path_key_public');
    }
    public function getClientStoragePathKeyDat()
    {
        return config('license.client.storage_path_dat');
    }

    // Resolve a licença (arquivo ou banco)
    private function getLicenseCode()
    {
        $filePath = getClientStoragePathDat();

        if (file_exists($filePath)) {
            if (filesize($filePath) > 0) {
                return getLicenseCodeFile(); // arquivo existe e não está vazio
            } else {
                \Log::warning("Arquivo de licença existe mas está vazio: $filePath");
                return false; // arquivo vazio
            }
        }

        $latestLicense = getLicenseCodeBd();
        if ($latestLicense) {
            return $latestLicense->license_code;
        }

        return false; // nem arquivo válido nem registro no banco
    }

    // Retorna os dados descriptografados
    public function getLicenseData(): array
    {
        $licenseCode = $this->getLicenseCode();

        if (!$licenseCode) {
            return ['status' => false, 'message' => 'Licença ausente ou não encontrada, por favor active a licença ou contacte o suporte administativo.'];
        }

        try {
            $dados = json_decode(decryptLicenseCode($licenseCode), true);
            if (!$dados) {
                return ['status' => false, 'message' => 'Não foi possível decodificar a licença'];
            }
            return ['status' => true, 'message' => 'Licença carregada com sucesso', 'data' => $dados];
        } catch (\Exception $e) {
            \Log::error("Erro ao decodificar licença: " . $e->getMessage());
            return ['status' => false, 'message' => 'Erro ao decodificar licença: ' . $e->getMessage()];
        }
    }

    // Verifica se está expirada
    private function isExpired(): bool
    {
        $licenseData = $this->getLicenseData();
        return now()->gt(Carbon::parse($licenseData['data']['expire_in']));
    }



    // Verifica se o hardware bate
    private function isHardwareValid(): bool
    {
        $licenseData = $this->getLicenseData();

        return $licenseData['data']['hardware_id'] === getHardwareFingerprint();
    }

    private function isPublicKeyValid(): bool
    {
        return file_exists(getClientStoragePathKeyPublic());
    }
    private function isRSASignatureValid(array $licenseData): bool
    {
        // Pega os dados reais da licença
        $data = $licenseData['data'];

        if (empty($data['rsa'])) {
            return false;
        }

        $publicKeyPath = getClientStoragePathKeyPublic();
        if (!file_exists($publicKeyPath)) {
            return false;
        }

        $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));
        $assinaturaRSA = base64_decode($data['rsa']);

        $dadosCriticos = json_encode([
            'client_name' => $data['client_name'],
            'hardware_id' => $data['hardware_id'],
            'expire_in' => $data['expire_in'],
        ]);

        return openssl_verify($dadosCriticos, $assinaturaRSA, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    private function isHmacValid(): bool
    {
        $licenseData = $this->getLicenseData();

        // Se não houver licença ou dados
        if (!$licenseData) {
            return false;
        }

        $data = $licenseData['data'];

        if (empty($data['hmac'])) {
            return false;
        }

        $dadosParaHMAC = $data;
        unset($dadosParaHMAC['hmac']);

        // Calcula assinatura
        $assinaturaCorreta = hash_hmac('sha256', json_encode($dadosParaHMAC), $this->extraSecret());

        // Compara com segurança
        return hash_equals($assinaturaCorreta, $data['hmac']);
    }



    //resposta de verificação expirada e hardware
    public function verificationStatus(): array
    {
        if (!$this->isPublicKeyValid()) {
            return ['status' => false, 'message' => 'Chave pública é inválida ou não existe, contate o suporte para adquirir uma nova chave!'];
        }
        $licenseData = $this->getLicenseData();
        if (!$licenseData['status']) {
            return ['status' => false, 'message' => $licenseData['message']];
        }

        if ($this->isExpired()) {
            return ['status' => false, 'message' => 'Licença expirada'];
        }
        if (!$this->isHardwareValid()) {
            return ['status' => false, 'message' => 'Licença inválida para este hardware ou máquina'];
        }

        if (!$this->isRSASignatureValid(getLicenseCodedecrypted())) {
            return ['status' => false, 'message' => 'Assinatura RSA inválida ou ausente!'];
        }
        if (!$this->isHmacValid()) {
            return ['status' => false, 'message' => 'Assinatura HMAC inválida!'];
        }

        return ['status' => true, 'message' => 'Licença válida'];
    }


    public function checkLicense(): array
    {

        $licenseData = $this->getLicenseData();

        $now = Carbon::now();
        $expire = Carbon::parse($licenseData['data']['expire_in']);
        $daysLeft = ceil($now->diffInDays($expire, false));

        return [
            'valid' => $daysLeft >= 0,
            'days_left' => $daysLeft
        ];
    }

    //Ativar a licença
    public function activateLicense(string $licenseCode): array
{
    if (!File::exists($this->getStorageKeys())) {
        return ['success' => false, 'message' => 'Chave pública não existe. Faça o upload primeiro, para ativar a licença.'];
    }

    try {
        $dados = json_decode(decryptLicenseCode($licenseCode), true);
    } catch (\Exception $e) {
        return ['success' => false, 'message' => 'Código de licença inválido!'];
    }

    if (!$dados) {
        return ['success' => false, 'message' => 'Código de licença inválido!'];
    }

    // 🔐 Segredo protegido e HMAC
    $segredoExtra = LicenseHelper::getSegredoExtra();
    $dadosParaAssinatura = $dados;
    unset($dadosParaAssinatura['hmac']);

    $assinaturaCorreta = hash_hmac('sha256', json_encode($dadosParaAssinatura), $segredoExtra);
    if (!hash_equals($assinaturaCorreta, $dados['hmac'])) {
        return ['success' => false, 'message' => 'Licença adulterada (HMAC inválido)!'];
    }

    // ✅ Verificação RSA
    if (!empty($dados['rsa'])) {
        $publicKeyPath = $this->getClientStoragePathKeyPublic();
        $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));
        $assinaturaRSA = base64_decode($dados['rsa']);

        if (openssl_verify(json_encode([
            'client_name' => $dados['client_name'],
            'hardware_id' => $dados['hardware_id'],
            'expire_in' => $dados['expire_in'],
        ]), $assinaturaRSA, $publicKey, OPENSSL_ALGO_SHA256) !== 1) {
            return ['success' => false, 'message' => 'Licença adulterada (Chave pública inválida)!'];
        }
    } else {
        return ['success' => false, 'message' => 'Licença inválida (sem assinatura RSA)!'];
    }

    // ⏳ Valida expiração
    if (now()->gt(Carbon::parse($dados['expire_in']))) {
        return ['success' => false, 'message' => 'Licença expirada!'];
    }

    // 💻 Valida hardware
    if ($dados['hardware_id'] !== getHardwareFingerprint()) {
        return ['success' => false, 'message' => 'Licença não corresponde a esta máquina!'];
    }

    // ✅ Salva no banco e arquivo
    License::updateOrCreate(
        ['id' => 1],
        [
            'license_code' => $licenseCode,
            'valid_until' => $dados['expire_in'],
        ]
    );

    file_put_contents(getClientStoragePathDat(), $licenseCode);

    return ['success' => true, 'message' => 'Licença ativada com sucesso! Válida até ' . $dados['expire_in']];
}


}
