<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor3S - Login</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <script src="https://kit.fontawesome.com/7a1c3e0b3f.js" crossorigin="anonymous"></script>
</head>
<body @if(session('register_open')) data-open-register="true" @endif>

<div class="login-container">

    <!-- LADO ESQUERDO -->
    <div class="branding-side">

        <div class="logo-wrapper">
            <img src="{{ asset('images/logo.png') }}" class="mini-logo">
            <span class="brand-name">Gestor3S</span>
        </div>

        <div class="branding-content">
            <h1>
                Organize suas tarefas <br>
                <span class="highlight">com eficiência</span>
            </h1>

            <p>
                Sistema de gestão de tarefas moderno, focado em produtividade,
                organização e colaboração em equipe.
            </p>

            <ul class="features-list">
                <li><span class="check-icon"></span> Quadros estilo Kanban</li>
                <li><span class="check-icon"></span> Colaboração em equipe</li>
                <li><span class="check-icon"></span> Etiquetas e prioridades</li>
            </ul>
        </div>

        <footer class="branding-footer">
            © 2026 Gestor3S
        </footer>

    </div>

    <!-- LADO DIREITO -->
    <div class="form-side">

        <div class="login-card">

            <div class="form-logo-mobile">
                <img src="{{ asset('images/logo.png') }}">
            </div>

            <h2>Bem-vindo de volta</h2>
            <p class="subtitle">Entre na sua conta</p>

            <!-- ERRO -->
            @if ($errors->any())
                <div class="error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('registered'))
                <div class="success-box">
                    {{ session('registered') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="input-group">
                    <label>E-mail</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" placeholder="seu@email.com" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Senha</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Sua senha" required>
                        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword()"></i>
                    </div>
                </div>

                <div class="form-options">
                    <label>
                        <input type="checkbox" name="remember"> Lembrar
                    </label>
                    <a href="#">Esqueceu?</a>
                </div>

                <button type="submit" class="btn-primary">Entrar</button>

                <div class="divider">ou</div>

                <button type="button" id="open-register" class="btn-secondary">Criar conta</button>
            </form>

        </div>

    </div>

</div>

<!-- MODAL DE CADASTRO -->
<div class="overlay" id="registerOverlay" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="registerTitle">
        <div class="modal-header">
            <div class="modal-title" id="registerTitle">Crie sua conta</div>
            <button class="close-btn" id="closeRegister" aria-label="Fechar">✕</button>
        </div>

        <div class="modal-body">
            @if ($errors->any() && session('register_open'))
                <div class="error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="_from" value="register">

                <div class="input-group">
                    <label>Nome</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="name" placeholder="Seu nome" value="{{ old('name') }}" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>E-mail</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" placeholder="seu@email.com" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Senha</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="Sua senha" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Confirme a senha</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password_confirmation" placeholder="Confirme a senha" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Foto (opcional)</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-image"></i>
                        <input type="file" id="fotoInput" name="foto" accept="image/*">
                    </div>
                    <div style="margin-top:8px; display:flex; gap:8px; align-items:center;">
                        <img id="fotoPreview" src="" alt="Pré-visualização" style="width:56px;height:56px;border-radius:8px;object-fit:cover;display:none;border:1px solid #e5e7eb;" />
                        <small id="fotoNome" style="color:#64748b;display:none;"></small>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Criar conta</button>
            </form>
        </div>
    </div>
</div>

        </div>

    </div>

</div>

<script src="{{ asset('js/login.js') }}"></script>

</body>
</html>