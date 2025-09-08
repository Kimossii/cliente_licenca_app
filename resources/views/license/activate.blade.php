<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<h2>Ativar Licença</h2>
@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif
@if(session('error'))
    <p style="color:red">{{ session('error') }}</p>
@endif

<form method="POST" action="{{ route('license.activate') }}">
    @csrf
    <label>Insira a sua License Code:</label><br>
    <textarea name="license_code" cols="80" rows="5" required></textarea><br><br>
    <div style="display: flex; gap: 10px;">
        <button type="submit" name="action" value="activate" style="flex: 1; min-width: 80px; max-width: 140px; padding: 6px 12px; font-size: 14px;">Ativar</button>
        <a t href="{{route('license.request')}}" style="flex: 1; min-width: 80px; max-width: 140px; padding: 6px 12px; font-size: 14px;">Código de Ativação</a>
        <a href="{{route('import.uploadKey')}}" style="flex: 1; min-width: 80px; max-width: 140px; padding: 6px 12px; font-size: 14px;">Importar chave pública</a>
    </div>
    </div>
</form>

</body>
</html>
