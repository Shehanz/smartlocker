<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login · BlueVault</title>
    <style>
        :root{--blue:#2563eb;--blue2:#60a5fa;--ink:#090d17;--muted:#64748b;--line:#e5eaf2;--paper:#fff;--wash:#f3f6fb}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at 15% 15%,#dbeafe 0,transparent 30%),radial-gradient(circle at 90% 85%,#dbeafe 0,transparent 28%),var(--wash);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--ink)}
        .shell{width:min(980px,100%);display:grid;grid-template-columns:1fr 1fr;background:#fff;border:1px solid #e4eaf3;border-radius:26px;overflow:hidden;box-shadow:0 26px 80px #0f172a14}.brand{position:relative;overflow:hidden;padding:48px;background:#090d17;color:white;display:flex;flex-direction:column;justify-content:space-between;min-height:550px}.brand:before,.brand:after{content:"";position:absolute;border:1px solid #60a5fa35;border-radius:50%;width:330px;height:330px;right:-155px;top:65px}.brand:after{width:230px;height:230px;right:-100px;top:115px;border-color:#60a5fa65}.mark{width:48px;height:48px;display:grid;place-items:center;border-radius:15px;background:#2563eb;font-weight:900;font-size:22px;box-shadow:0 8px 30px #2563eb50}.eyebrow{letter-spacing:.18em;text-transform:uppercase;color:#93c5fd;font-weight:800;font-size:11px}.brand h1{font-size:clamp(34px,4vw,48px);line-height:1.06;letter-spacing:-.04em;max-width:370px;margin:14px 0}.brand p{color:#cbd5e1;line-height:1.7;max-width:350px;font-size:14px}.brand-foot{display:flex;align-items:center;gap:10px;color:#94a3b8;font-size:12px}.dot{height:8px;width:8px;border-radius:50%;background:#60a5fa;box-shadow:0 0 0 5px #60a5fa20}.form-side{padding:clamp(28px,5vw,58px);display:flex;flex-direction:column;justify-content:center}.form-side h2{font-size:28px;letter-spacing:-.035em;margin:0 0 8px}.sub{color:var(--muted);font-size:14px;margin:0 0 30px;line-height:1.6}.field{margin:17px 0}.field label{display:block;font-size:12px;font-weight:750;margin-bottom:8px;color:#334155}.field input{width:100%;padding:13px 14px;border:1px solid #dbe3ef;border-radius:12px;font:inherit;font-size:14px;outline:none;transition:.2s;background:#fbfcfe}.field input:focus{border-color:var(--blue);box-shadow:0 0 0 4px #2563eb16;background:#fff}.btn{width:100%;border:0;border-radius:12px;background:var(--blue);color:#fff;padding:14px 16px;font-weight:800;cursor:pointer;box-shadow:0 8px 20px #2563eb30;transition:.2s}.btn:hover{background:#1d4ed8;transform:translateY(-1px)}.error{border:1px solid #fecaca;background:#fff1f2;color:#be123c;border-radius:12px;padding:12px 13px;font-size:13px;margin-bottom:18px}.secure{margin-top:25px;color:#94a3b8;font-size:11px;text-align:center}@media(max-width:700px){.shell{grid-template-columns:1fr}.brand{min-height:230px;padding:28px}.brand h1{font-size:33px}.brand .spacer{display:none}.brand:before{top:-150px}.brand:after{top:-100px}.form-side{padding:30px}}
    </style>
</head>
<body>
<div class="shell">
    <section class="brand">
        <div class="mark">B</div>
        <div class="spacer"></div>
        <div>
            <div class="eyebrow">Smart locker system</div>
            <h1>Access your space.<br>Keep it secure.</h1>
            <p>Kelola kartu RFID, siswa, dan hak akses loker dari satu dashboard yang terhubung ke Firebase.</p>
        </div>
        <div class="brand-foot"><span class="dot"></span> Secure administration portal</div>
    </section>
    <section class="form-side">
        <h2>Selamat datang</h2>
        <p class="sub">Login dengan akun administrator untuk melanjutkan.</p>
        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('locker.login.submit') }}">
            @csrf
            <div class="field"><label for="username">USERNAME</label><input id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus placeholder="Masukkan username"></div>
            <div class="field"><label for="password">PASSWORD</label><input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Masukkan password"></div>
            <button class="btn" type="submit">Login ke Dashboard &rarr;</button>
        </form>
        <div class="secure">BlueVault · Smart Locker Admin</div>
    </section>
</div>
</body>
</html>
