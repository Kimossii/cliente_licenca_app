<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<h2>Seu Request Code</h2>
<textarea id="requestCode" cols="80" rows="5" readonly>{{ $requestCode }}</textarea>
<p>Copie este código e envie ao administrador para gerar sua licença.</p>
<button onclick="copiarCodigo()">Copiar código</button>
<span id="mensagem" style="margin-left:10px;color:green;"></span>

<a href="{{ route('license.activate.form') }}"
   style="flex: 1; min-width: 80px; max-width: 140px; padding: 6px 12px; font-size: 14px;">
   Ativar licença
</a>

<script>
function copiarCodigo() {
    var textarea = document.getElementById('requestCode');

   
    var tempInput = document.createElement('textarea');
    tempInput.value = textarea.value;
    document.body.appendChild(tempInput);
    tempInput.select();

    try {
        var successful = document.execCommand('copy');
        var msg = document.getElementById('mensagem');
        if (successful) {
            msg.textContent = 'Código copiado!';
        } else {
            msg.textContent = 'Falha ao copiar!';
        }
        setTimeout(() => msg.textContent = '', 2000);
    } catch (err) {
        alert('Erro ao copiar o código.');
        console.error(err);
    }


    document.body.removeChild(tempInput);
}
</script>

</html>


