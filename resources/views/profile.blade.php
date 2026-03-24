<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>

<div style="max-width:820px;margin:24px auto;background:white;padding:20px;border-radius:12px">
    <h2>Meu Perfil</h2>

    @if(session('success'))
        <div style="background:#ecfdf5;color:#065f46;padding:8px;border-radius:6px;margin-bottom:10px">{{ session('success') }}</div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px">
            @if($user && $user->foto)
                <img src="{{ asset('storage/'.$user->foto) }}" alt="avatar" style="width:72px;height:72px;border-radius:8px;object-fit:cover">
            @else
                <div style="width:72px;height:72px;border-radius:8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center">{{ strtoupper(substr($user->name,0,1)) }}</div>
            @endif
            <div style="display:flex;flex-direction:column;gap:6px">
                <label>Alterar foto</label>
                <input type="file" name="foto" accept="image/*">
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px">
            <label>Nome</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" style="padding:8px;border-radius:6px;border:1px solid #e5e7eb">

            <label>E-mail</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" style="padding:8px;border-radius:6px;border:1px solid #e5e7eb">

            <div style="margin-top:12px;display:flex;gap:8px">
                <button class="btn-primary" type="submit">Salvar</button>
                <a href="{{ route('dashboard') }}" class="btn-secondary">Voltar</a>
            </div>
        </div>
    </form>
</div>

</body>
</html>