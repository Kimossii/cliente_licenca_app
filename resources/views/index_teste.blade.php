<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <nav style="background: #222; color: #fff; padding: 1rem;">
        <ul style="list-style: none; display: flex; gap: 2rem; margin: 0; padding: 0;">
            <li><a href="#" style="color: #fff; text-decoration: none;">Início</a></li>
            <li><a href="#" style="color: #fff; text-decoration: none;">Sobre</a></li>
            <li><a href="#" style="color: #fff; text-decoration: none;">Serviços</a></li>
            <li><a href="#" style="color: #fff; text-decoration: none;">Contato</a></li>
        </ul>
    </nav>
    <main style="padding: 2rem;">

        @if (session('success'))
            <div style="background: #28a745; color: #fff; padding: 1rem 1.5rem; border-radius: 6px; box-shadow: 0 2px 8px rgba(40,167,69,0.15); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <svg width="24" height="24" fill="none" style="flex-shrink:0;" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="12" fill="#fff" opacity="0.15"/>
                <path d="M9.5 13.5l2 2 4-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span style="font-size: 1.1rem; font-weight: 500;">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <p style="color:red">{{ session('error') }}</p>
        @endif

        @if (session('warning'))
            <div style="background: #ffc107; color: #222; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
                {{ session('warning') }}
            </div>
        @endif
        <h1>Bem-vindo ao Notino</h1>
        <p>Este é um índice de exemplo com menus e tudo mais.</p>
        <section>
            <h2>Produtos em destaque</h2>
            <ul>
                <li>Perfume Masculino</li>
                <li>Perfume Feminino</li>
                <li>Cuidados com a pele</li>
                <li>Maquiagem</li>
            </ul>
        </section>
        <section>
            <h2>Novidades</h2>
            <p>Confira as últimas novidades e promoções!</p>
        </section>
    </main>
    <footer
        style="background: #222; color: #fff; text-align: center; padding: 1rem; position: fixed; width: 100%; bottom: 0;">
        &copy; {{ date('Y') }} Notino. Todos os direitos reservados.
    </footer>
</body>

</html>
