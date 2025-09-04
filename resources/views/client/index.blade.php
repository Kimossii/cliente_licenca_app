<!DOCTYPE html>
<html>
<head>
    <title>Validar Licença</title>
</head>
<body>
    <h2>Validar Licença</h2>

    @if(session('success'))
        <div style="color: green; font-weight: bold;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div style="color: red; font-weight: bold;">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('license.validate') }}">
        @csrf
        <label for="license_key">Cole sua licença aqui:</label><br>
        <input type="text" name="license_key" id="license_key" style="width: 100%;" required>
        <br><br>
        <button type="submit">Validar</button>
    </form>
</body>
</html>
