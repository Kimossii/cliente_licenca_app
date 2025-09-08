<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Chave Pública</title>
</head>

<body>
    <h1>Importar a chave pública</h1>

    @if (session('success'))
        <div style="margin-top:10px; color: green;">
            {{ session('success') }}
        </div>
    @endif

    @if ($chaveExiste)
        <div style="margin-top:10px; color: orange;">
            Chave pública já existe. Deseja substituir?
        </div>
        <form id="uploadKeyForm" action="{{ route('client.uploadKey') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="overwrite" value="1">
            <label for="public_key">Enviar Public Key (.pem):</label>
            <input type="file" id="public_key" name="public_key" accept=".pem" required onchange="previewFile()">
            <br>
            <button type="submit" style="margin-top: 10px;">Substituir Chave</button>
        </form>
    @else
        <form id="uploadKeyForm" action="{{ route('client.uploadKey') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label for="public_key">Enviar Public Key (.pem):</label>
            <input type="file" id="public_key" name="public_key" accept=".pem" required onchange="previewFile()">
            <div id="filePreview" style="margin-top: 10px; font-family: monospace; white-space: pre-wrap;"></div>
            <br>
            <button type="submit" style="margin-top: 10px;">Enviar Chave</button>
        </form>
    @endif





</body>
<script>
        function previewFile() {
            const preview = document.getElementById('filePreview');
            const file = document.getElementById('public_key').files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.textContent = e.target.result.substring(0, 500) + (e.target.result.length > 500 ? '...' :
                        '');
                }
                reader.readAsText(file);
            } else {
                preview.textContent = '';
            }
        }
    </script>

</html>
