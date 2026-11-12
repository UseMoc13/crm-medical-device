<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — CRM Medical Device</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#f3f6fa;color:#17284f;font-family:Inter,Arial,sans-serif;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{width:100%;max-width:430px;background:#fff;border:1px solid #dfe6f0;border-radius:18px;box-shadow:0 18px 50px rgba(23,40,79,.09);padding:36px}
        .brand{display:flex;align-items:center;gap:12px;margin-bottom:32px}.mark{background:#17284f;color:white;width:42px;height:42px;display:grid;place-items:center;border-radius:12px;font-weight:800;font-size:23px}
        .brand strong{display:block;font-size:15px}.brand small{display:block;font-size:11px;color:#68778c;margin-top:3px}
        h1{font-size:26px;margin:0 0 8px}p{color:#69788e;font-size:13px;line-height:1.6;margin:0 0 28px}
        label{display:block;font-weight:600;font-size:13px;margin-bottom:8px}input{width:100%;padding:12px 14px;border:1px solid #d6dfeb;border-radius:9px;font-size:14px;color:#17284f;outline:none}input:focus{border-color:#2ba7a0;box-shadow:0 0 0 3px rgba(43,167,160,.12)}
        .field{margin-bottom:20px}.error{border:1px solid #ffd2d2;background:#fff1f1;color:#a3333a;border-radius:9px;padding:12px 14px;font-size:12px;margin-bottom:20px;line-height:1.55}
        button{width:100%;padding:13px;background:#17284f;color:white;border:0;border-radius:9px;cursor:pointer;font-size:14px;font-weight:700}button:hover{background:#244071}.footer{font-size:11px;color:#8491a4;text-align:center;margin-top:25px}
    </style>
</head>
<body>
<main class="card">
    <div class="brand"><div class="mark">+</div><div><strong>CRM Medical Device</strong><small>Company Workspace</small></div></div>
    <h1>Welcome back</h1>
    <p>Sign in using your company account to continue.</p>
    @if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" maxlength="150" required autofocus></div>
        <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <button type="submit">Sign in</button>
    </form>
    <div class="footer">Authorized personnel only · CRM Medical Device</div>
</main>
</body>
</html>
