<!DOCTYPE html>
<html>
<head>
    <title>Gerar Código</title>
</head>
<body>
    <h2>Gerar Fingerprint</h2>

    <form method="POST" action="{{ url('/cliente/gerar-codigo') }}">
        @csrf
        <button type="submit">Gerar Código</button>
    </form>

    @if(isset($fingerprint))
        <p><strong>Fingerprint gerado:</strong></p>
        <textarea readonly style="width: 100%; height: 80px;">{{ $fingerprint }}</textarea>
        <p>Copie este código e envie para o fornecedor para gerar a licença.</p>
    @endif
</body>
</html>
