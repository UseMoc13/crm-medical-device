<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — CRM Medical Device</title>
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:grid;place-items:center;padding:28px;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;background:linear-gradient(140deg,#eef3f8,#f9fbfd);color:#17284f}
        .login-shell{width:min(100%,430px)}
        .brand{text-align:center;margin-bottom:25px}.brand-icon{display:grid;place-items:center;width:64px;height:64px;border-radius:17px;background:#17284f;color:#fff;font-size:23px;font-weight:800;margin:0 auto 15px;box-shadow:0 12px 30px #17284f25}
        h1{font-size:25px;line-height:1.25;margin:0 0 8px}.brand p{color:#748198;font-size:13px;margin:0}
        .login-card{background:#fff;border:1px solid #e1e7ef;border-radius:15px;padding:31px;box-shadow:0 20px 60px #17284f12}
        .heading{font-size:17px;font-weight:750;margin:0 0 7px}.sub{font-size:12px;line-height:1.65;color:#738096;margin:0 0 26px}
        .field{margin-bottom:18px}.field label{display:block;font-weight:650;font-size:12px;margin-bottom:8px}
        input{height:45px;width:100%;border:1px solid #d9e0ea;border-radius:8px;padding:0 13px;color:#17284f;font-size:13px;background:#fff}
        input:focus{outline:none;border-color:#2ba7a0;box-shadow:0 0 0 3px #2ba7a016}
        button{width:100%;height:44px;border:0;border-radius:8px;background:#168b85;color:#fff;font-size:13px;font-weight:750;cursor:pointer}
        button:hover{background:#117872}
        .notice{padding:12px 14px;font-size:12px;line-height:1.6;border-radius:8px;margin-bottom:18px}
        .notice.warn{background:#fff8e8;color:#946200;border:1px solid #f4deae}
        .notice.error{background:#fff2f2;color:#b33942;border:1px solid #f5cccc}
        .notice.ok{background:#eaf7f3;color:#13785f;border:1px solid #c5e5d8}
        .field-error{color:#b33942;font-size:11px;margin-top:6px}
        footer{font-size:11px;color:#8a96a7;text-align:center;margin-top:22px}
        @media(max-width:480px){.login-card{padding:23px}}
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="brand">
            <div class="brand-icon" aria-hidden="true">CRM</div>
            <h1>CRM Medical Device</h1>
            <p>PT Radja Usada Mandiri</p>
        </div>
        <div class="login-card">
            <h2 class="heading">Sign in to your workspace</h2>
            <p class="sub">Use your registered CRM account to continue managing your work.</p>
            @if(session('warning')) <div class="notice warn" role="status">{{ session('warning') }}</div> @endif
            @if(session('status')) <div class="notice ok" role="status">{{ session('status') }}</div> @endif
            @if($errors->any())
                <div class="notice error" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="field">
                    <label for="email">Email address</label>
                    <input type="email" name="email" id="email" autocomplete="username" maxlength="255" required autofocus value="{{ old('email') }}">
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="current-password" required>
                    @error('password') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <button type="submit">Sign In</button>
            </form>
        </div>
        <footer>© {{ date('Y') }} PT Radja Usada Mandiri · Internal CRM</footer>
    </div>
</body>
</html>
