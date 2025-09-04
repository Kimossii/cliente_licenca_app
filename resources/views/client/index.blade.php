<!DOCTYPE html>
<html>

<head>
    <title>Validar Licença</title>
</head>

<body>
    <h2>Validar Licença</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first('license_key') }}</div>
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
