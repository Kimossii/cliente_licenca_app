<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LicenseService;

class LicenseCheck
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Rotas de ativação/licença
        $activationRoutes = [
            'license.activate.form',
            'license.activate',
            'client.uploadKey',
            'license.request',
            'import.uploadKey'
        ];

        // Nome da rota atual
        $routeName = $request->route() ? $request->route()->getName() : null;

        // Verifica a licença
        $status = $this->licenseService->verificationStatus();

        // Licença inválida → permite apenas rotas de ativação
        if (!$status['status']) {
            if (in_array($routeName, $activationRoutes)) {
                // rota de ativação permitida
                return $next($request);
            }

            // qualquer outra rota → redireciona para ativação
            return redirect()->route('license.activate.form')
                ->with('error', $status['message']);
        }

        // Licença válida → bloqueia rotas de ativação
        if (in_array($routeName, $activationRoutes)) {
            return redirect()->route('index') // ou dashboard
                ->with('info', 'Licença já ativa.');
        }

        // Rotas normais, licença válida
        return $next($request);
    }
}

