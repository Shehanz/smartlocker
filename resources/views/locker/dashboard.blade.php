<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BlueVault · Admin Dashboard</title>
    <style>
        :root{--blue:#2563eb;--blue-dark:#1d4ed8;--blue-soft:#eff6ff;--ink:#0b1020;--muted:#64748b;--line:#e7ebf2;--paper:#fff;--wash:#f5f7fb;--green:#15803d;--green-bg:#dcfce7;--red:#b91c1c;--red-bg:#fee2e2;--amber:#92400e;--amber-bg:#fef3c7;--nav:#090d17}
        *{box-sizing:border-box}body{margin:0;background:var(--wash);color:var(--ink);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;font-size:14px}button,input,select{font:inherit}button{cursor:pointer}.layout{min-height:100vh;display:grid;grid-template-columns:250px minmax(0,1fr)}.sidebar{background:var(--nav);color:#fff;padding:25px 17px;display:flex;flex-direction:column;gap:28px;min-height:100vh}.brand{display:flex;gap:11px;align-items:center;padding:0 9px}.brand-mark{width:42px;height:42px;display:grid;place-items:center;background:var(--blue);border-radius:13px;font-size:20px;font-weight:900;box-shadow:0 7px 20px #2563eb55}.brand-name{font-size:16px;font-weight:850;letter-spacing:-.03em}.brand-sub{font-size:10px;color:#94a3b8;margin-top:3px;letter-spacing:.14em;text-transform:uppercase}.nav-label{font-size:10px;letter-spacing:.15em;font-weight:800;color:#64748b;padding:0 12px;margin-bottom:8px}.nav{display:grid;gap:5px}.nav button{border:0;background:transparent;color:#a8b3c7;text-align:left;border-radius:11px;padding:12px;display:flex;gap:12px;align-items:center;font-size:13px;font-weight:700;transition:.16s}.nav button:hover{background:#ffffff0b;color:white}.nav button.active{background:#2563eb;color:white;box-shadow:0 7px 18px #2563eb2b}.nav-icon{width:20px;display:inline-grid;place-items:center;font-size:16px}.side-bottom{margin-top:auto;border:1px solid #ffffff17;border-radius:15px;padding:14px;background:#ffffff08}.side-bottom strong{display:block;font-size:12px}.side-bottom p{font-size:11px;color:#94a3b8;line-height:1.5;margin:7px 0 0}.main{min-width:0}.topbar{height:78px;background:#ffffffeb;border-bottom:1px solid var(--line);padding:0 32px;display:flex;align-items:center;justify-content:space-between;gap:16px;position:sticky;top:0;z-index:4;backdrop-filter:blur(12px)}.crumb{font-size:11px;color:var(--muted);margin-bottom:4px}.top-title{font-size:18px;font-weight:850;letter-spacing:-.035em}.top-right{display:flex;align-items:center;gap:12px}.system-pill{display:flex;align-items:center;gap:8px;padding:8px 11px;border:1px solid var(--line);border-radius:999px;background:white;font-size:11px;font-weight:750;color:#334155}.live-dot{height:7px;width:7px;background:#22c55e;border-radius:50%;box-shadow:0 0 0 4px #22c55e18}.avatar{height:36px;width:36px;background:#dbeafe;color:#1d4ed8;border-radius:12px;display:grid;place-items:center;font-weight:900}.logout{border:1px solid var(--line);background:white;border-radius:10px;padding:9px 12px;font-size:12px;font-weight:750;color:#334155}.content{padding:30px 32px 42px;max-width:1500px;margin:auto}.intro{display:flex;justify-content:space-between;gap:20px;align-items:flex-end;margin-bottom:24px}.eyebrow{color:var(--blue);font-size:10px;font-weight:900;letter-spacing:.16em;text-transform:uppercase}.intro h1{font-size:27px;letter-spacing:-.05em;margin:8px 0 7px}.intro p{color:var(--muted);margin:0;line-height:1.6}.btn{border:0;border-radius:10px;background:var(--blue);color:white;padding:11px 15px;font-weight:800;font-size:12px;box-shadow:0 5px 13px #2563eb27}.btn:hover{background:var(--blue-dark)}.btn:disabled{opacity:.55;cursor:wait}.btn-light{border:1px solid var(--line);background:white;color:#334155;border-radius:9px;padding:8px 10px;font-size:11px;font-weight:750}.btn-danger{border:1px solid #fecaca;background:#fff7f7;color:var(--red);border-radius:9px;padding:8px 10px;font-size:11px;font-weight:750}.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:24px}.stat{border:1px solid var(--line);border-radius:16px;padding:19px;background:#fff;position:relative;overflow:hidden}.stat-top{display:flex;justify-content:space-between;align-items:center;gap:10px}.stat-name{font-size:12px;color:var(--muted);font-weight:700}.stat-icon{height:37px;width:37px;border-radius:12px;background:var(--blue-soft);color:var(--blue);display:grid;place-items:center;font-size:17px}.stat-value{font-size:30px;font-weight:900;letter-spacing:-.05em;margin:14px 0 2px}.stat-note{font-size:11px;color:#94a3b8}.panel{background:white;border:1px solid var(--line);border-radius:16px;overflow:hidden;margin-bottom:18px}.panel-head{padding:19px 21px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:14px}.panel-head h2{margin:0;font-size:15px;letter-spacing:-.02em}.panel-head p{margin:5px 0 0;color:var(--muted);font-size:11px}.panel-body{padding:20px}.section{display:none}.section.active{display:block}.quick-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.quick{border:1px solid var(--line);border-radius:13px;padding:17px;text-align:left;background:#fff;display:flex;gap:13px;align-items:flex-start}.quick:hover{border-color:#93c5fd;background:#fafdff}.quick-icon{height:39px;width:39px;flex:0 0 39px;background:var(--blue-soft);color:var(--blue);display:grid;place-items:center;border-radius:12px;font-size:17px}.quick strong{display:block;font-size:13px}.quick span{font-size:11px;color:var(--muted);line-height:1.5;display:block;margin-top:5px}.grid-two{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(300px,.9fr);gap:18px}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.field{min-width:0}.field.full{grid-column:1/-1}.field label{display:block;font-size:11px;font-weight:800;color:#334155;margin-bottom:7px}.field input,.field select{width:100%;height:42px;border:1px solid #dce3ee;border-radius:10px;padding:0 12px;background:#fbfcfe;color:#0f172a;outline:none}.field input:focus,.field select:focus{border-color:#60a5fa;box-shadow:0 0 0 3px #2563eb14;background:white}.field-hint{font-size:10px;color:#94a3b8;margin-top:6px;line-height:1.5}.check-row{display:flex;align-items:center;gap:9px;color:#334155;font-size:12px;font-weight:700}.check-row input{accent-color:var(--blue);height:15px;width:15px}.form-actions{display:flex;gap:9px;margin-top:17px;flex-wrap:wrap}.table-wrap{overflow:auto;width:100%}table{width:100%;border-collapse:collapse;white-space:nowrap}thead{background:#f8fafc}th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;padding:13px 17px;font-weight:850;border-bottom:1px solid var(--line)}td{padding:14px 17px;border-bottom:1px solid #eff2f7;color:#334155;font-size:12px}tbody tr:last-child td{border-bottom:0}tbody tr:hover{background:#fbfdff}.identity{display:flex;align-items:center;gap:10px}.mini-avatar{height:31px;width:31px;border-radius:10px;background:#eff6ff;color:#2563eb;display:grid;place-items:center;font-weight:900;font-size:11px;flex:0 0 31px}.identity strong{display:block;color:#0f172a;font-size:12px}.identity span{display:block;color:#94a3b8;font-size:10px;margin-top:3px}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.04em;font-size:11px}.badge{display:inline-flex;align-items:center;gap:6px;padding:5px 8px;border-radius:999px;font-size:10px;font-weight:850}.badge:before{content:"";width:5px;height:5px;border-radius:50%;background:currentColor}.badge.active{background:var(--green-bg);color:var(--green)}.badge.inactive{background:var(--red-bg);color:var(--red)}.badge.pending{background:var(--amber-bg);color:var(--amber)}.actions{display:flex;gap:7px;align-items:center}.muted{color:var(--muted)}.empty{padding:36px 18px;text-align:center;color:var(--muted);font-size:12px}.empty strong{display:block;color:#334155;font-size:13px;margin-bottom:5px}.notice{border:1px solid #bfdbfe;background:#eff6ff;color:#1e40af;border-radius:12px;padding:12px 14px;font-size:12px;line-height:1.6;margin-bottom:18px}.toast{position:fixed;right:22px;bottom:22px;z-index:20;max-width:min(420px,calc(100vw - 44px));border-radius:13px;padding:14px 16px;background:#0b1020;color:white;box-shadow:0 15px 40px #0f172a2c;font-size:12px;line-height:1.5;transform:translateY(14px);opacity:0;pointer-events:none;transition:.2s}.toast.show{transform:translateY(0);opacity:1}.toast.error{background:#991b1b}.toolbar{display:flex;gap:9px;align-items:center}.search{border:1px solid var(--line);border-radius:10px;background:white;padding:10px 12px;min-width:190px;outline:none}.footer{padding-top:16px;color:#94a3b8;font-size:10px;display:flex;justify-content:space-between;gap:12px}
        @media(max-width:1100px){.layout{grid-template-columns:210px minmax(0,1fr)}.content{padding:24px 22px}.topbar{padding:0 22px}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.grid-two{grid-template-columns:1fr}}
        @media(max-width:760px){.layout{grid-template-columns:1fr}.sidebar{min-height:auto;padding:14px;gap:14px}.brand{padding:0 5px}.nav{display:flex;overflow:auto}.nav-label,.side-bottom{display:none}.nav button{white-space:nowrap;padding:10px 12px}.topbar{height:65px;padding:0 16px}.system-pill{display:none}.content{padding:21px 14px}.intro{align-items:flex-start;flex-direction:column}.intro h1{font-size:24px}.stats{gap:10px}.stat{padding:14px}.stat-value{font-size:26px}.quick-grid{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}.panel-head,.panel-body{padding:15px}.toolbar{width:100%;flex-wrap:wrap}.search{width:100%}.footer{flex-direction:column}}
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand"><div class="brand-mark">B</div><div><div class="brand-name">BlueVault</div><div class="brand-sub">Smart Locker Admin</div></div></div>
        <div><div class="nav-label">MENU UTAMA</div><nav class="nav" aria-label="Navigasi admin">
            <button class="active" data-section="overview"><span class="nav-icon">▦</span>Dashboard</button>
            <button data-section="students"><span class="nav-icon">♙</span>Data Siswa</button>
            <button data-section="cards"><span class="nav-icon">▤</span>Kartu RFID</button>
            <button data-section="lockers"><span class="nav-icon">▣</span>Manajemen Loker</button>
            <button data-section="logs"><span class="nav-icon">◷</span>Log Akses</button>
        </nav></div>
        <div class="side-bottom"><strong>● &nbsp; Firebase integration</strong><p>Data admin disinkronkan ke Firebase Realtime Database melalui Laravel.</p></div>
    </aside>
    <main class="main">
        <header class="topbar">
            <div><div class="crumb">SMART LOCKER &nbsp; / &nbsp; ADMIN PANEL</div><div id="topTitle" class="top-title">Dashboard overview</div></div>
            <div class="top-right"><div class="system-pill"><span class="live-dot"></span> Backend active</div><div class="avatar">{{ strtoupper(substr((string) session('locker_admin_username', 'A'), 0, 1)) }}</div><form method="POST" action="{{ route('locker.logout') }}">@csrf<button class="logout" type="submit">Keluar ↗</button></form></div>
        </header>
        <div class="content">
            <section class="section active" id="section-overview">
                <div class="intro"><div><div class="eyebrow">OVERVIEW</div><h1>Selamat datang, Admin.</h1><p>Kelola akses loker RFID dan pantau aktivitas sistem dari satu tempat.</p></div><button class="btn" onclick="refreshData(true)">↻ &nbsp; Refresh data</button></div>
                <div class="stats">
                    <div class="stat"><div class="stat-top"><span class="stat-name">Total siswa</span><span class="stat-icon">♙</span></div><div class="stat-value" id="statStudents">—</div><div class="stat-note">Siswa tersimpan di Firebase</div></div>
                    <div class="stat"><div class="stat-top"><span class="stat-name">Kartu aktif</span><span class="stat-icon">▤</span></div><div class="stat-value" id="statCards">—</div><div class="stat-note">UID yang diizinkan</div></div>
                    <div class="stat"><div class="stat-top"><span class="stat-name">Loker aktif</span><span class="stat-icon">▣</span></div><div class="stat-value" id="statLockers">—</div><div class="stat-note">Loker yang dapat digunakan</div></div>
                    <div class="stat"><div class="stat-top"><span class="stat-name">Akses ditolak</span><span class="stat-icon">⊘</span></div><div class="stat-value" id="statDenied">—</div><div class="stat-note">Total log yang termuat</div></div>
                </div>
                <div class="panel"><div class="panel-head"><div><h2>Akses cepat</h2><p>Pilih pekerjaan yang ingin kamu lakukan.</p></div></div><div class="panel-body"><div class="quick-grid">
                    <button class="quick" onclick="showSection('students')"><span class="quick-icon">♙</span><span><strong>Tambah siswa</strong><span>Daftarkan siswa dan tetapkan loker miliknya.</span></span></button>
                    <button class="quick" onclick="showSection('cards')"><span class="quick-icon">▤</span><span><strong>Daftarkan UID RFID</strong><span>Hubungkan UID baru atau pindahkan pemilik kartu.</span></span></button>
                    <button class="quick" onclick="showSection('lockers')"><span class="quick-icon">▣</span><span><strong>Tambah loker</strong><span>Pasangkan loker dengan perangkat ESP32.</span></span></button>
                </div></div></div>
                <div class="panel"><div class="panel-head"><div><h2>Aktivitas terbaru</h2><p>Log terbaru yang tersimpan di Firebase.</p></div><button class="btn-light" onclick="showSection('logs')">Lihat semua log →</button></div><div class="table-wrap"><table><thead><tr><th>Waktu</th><th>UID</th><th>Loker</th><th>Perangkat</th><th>Hasil</th></tr></thead><tbody id="recentLogs"><tr><td colspan="5" class="empty">Memuat data…</td></tr></tbody></table></div></div>
            </section>

            <section class="section" id="section-students">
                <div class="intro"><div><div class="eyebrow">DIRECTORY</div><h1>Data Siswa</h1><p>Atur identitas, status, dan loker yang menjadi hak setiap siswa.</p></div><button class="btn" onclick="resetStudentForm()">＋ Siswa baru</button></div>
                <div class="notice">Satu loker hanya dapat dimiliki satu siswa aktif pada satu waktu. Untuk melepas loker, edit siswa dan pilih “Tanpa loker”.</div>
                <div class="grid-two">
                    <div class="panel"><div class="panel-head"><div><h2 id="studentFormTitle">Tambah siswa</h2><p>ID siswa sebaiknya unik dan tidak berubah.</p></div></div><div class="panel-body"><form id="studentForm"><div class="form-grid">
                        <div class="field"><label for="studentId">ID SISWA</label><input id="studentId" required maxlength="40" placeholder="Contoh: S001"></div>
                        <div class="field"><label for="studentName">NAMA LENGKAP</label><input id="studentName" required maxlength="120" placeholder="Nama siswa"></div>
                        <div class="field"><label for="studentClass">KELAS</label><input id="studentClass" maxlength="80" placeholder="Contoh: XII RPL 1"></div>
                        <div class="field"><label for="studentLocker">LOKER</label><select id="studentLocker"><option value="">Tanpa loker</option></select></div>
                        <div class="field full"><label class="check-row"><input id="studentActive" type="checkbox" checked> Siswa aktif</label><div class="field-hint">Siswa nonaktif tidak dapat membuka loker walaupun kartu masih aktif.</div></div>
                    </div><div class="form-actions"><button class="btn" type="submit" id="studentSubmit">Simpan siswa</button><button class="btn-light" type="button" id="studentCancel" onclick="resetStudentForm()" hidden>Batal edit</button></div></form></div></div>
                    <div class="panel"><div class="panel-head"><div><h2>Catatan pengelolaan</h2><p>Tips menjaga data akses tetap konsisten.</p></div></div><div class="panel-body"><div class="notice">Mengubah loker siswa akan memperbarui data pemilik pada node siswa dan node loker melalui backend.</div><p class="muted" style="line-height:1.8;font-size:12px">• Nonaktifkan siswa yang sudah tidak menggunakan sistem.<br>• Jangan memakai ulang ID siswa untuk orang lain.<br>• Pastikan loker sudah terdaftar sebelum menugaskannya ke siswa.<br>• UID kartu dan pemiliknya diatur pada menu Kartu RFID.</p></div></div>
                </div>
                <div class="panel"><div class="panel-head"><div><h2>Daftar siswa</h2><p id="studentCount">Data dari Firebase Realtime Database.</p></div><input class="search" id="studentSearch" placeholder="Cari nama / ID siswa…"></div><div class="table-wrap"><table><thead><tr><th>Siswa</th><th>Kelas</th><th>Loker</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="studentsTable"></tbody></table></div></div>
            </section>

            <section class="section" id="section-cards">
                <div class="intro"><div><div class="eyebrow">CREDENTIALS</div><h1>Kartu RFID</h1><p>Kelola UID kartu, ganti pemilik, dan nonaktifkan kartu yang hilang.</p></div></div>
                <div class="notice"><b>Pindah pemilik:</b> masukkan UID yang sama lalu pilih siswa baru. Sistem akan memperbarui tautan kepemilikan di Firebase. Pastikan kartu benar-benar milik siswa baru sebelum menyimpan.</div>
                <div class="grid-two">
                    <div class="panel"><div class="panel-head"><div><h2>Daftarkan / pindahkan kartu</h2><p>UID disimpan di Firebase, bukan di firmware ESP32.</p></div></div><div class="panel-body"><form id="cardForm"><div class="form-grid">
                        <div class="field full"><label for="cardUid">UID KARTU RFID</label><input id="cardUid" required maxlength="80" placeholder="Contoh: 04 A1 B2 C3"><div class="field-hint">Boleh pakai spasi, titik dua, atau tanda hubung. Akan dinormalisasi menjadi heksadesimal tanpa pemisah.</div></div>
                        <div class="field full"><label for="cardStudent">PEMILIK KARTU</label><select id="cardStudent" required><option value="">Pilih siswa</option></select></div>
                        <div class="field full"><label class="check-row"><input id="cardActive" type="checkbox" checked> Kartu aktif</label></div>
                    </div><div class="form-actions"><button class="btn" type="submit" id="cardSubmit">Simpan kartu</button><button class="btn-light" type="button" onclick="resetCardForm()">Bersihkan</button></div></form></div></div>
                    <div class="panel"><div class="panel-head"><div><h2>Format UID</h2><p>Contoh cara penulisan UID.</p></div></div><div class="panel-body"><p class="muted" style="line-height:1.8;font-size:12px">UID dari RC522 sering ditampilkan dengan spasi di Serial Monitor. Contoh <span class="mono">04 A1 B2 C3</span> akan disimpan sebagai <span class="mono">04A1B2C3</span>. Gunakan nilai UID hasil pembacaan kartu sebenarnya, bukan UID contoh dalam kode.</p></div></div>
                </div>
                <div class="panel"><div class="panel-head"><div><h2>Kartu terdaftar</h2><p id="cardCount">UID dan pemiliknya.</p></div><input class="search" id="cardSearch" placeholder="Cari UID / pemilik…"></div><div class="table-wrap"><table><thead><tr><th>UID</th><th>Pemilik</th><th>Loker</th><th>Status</th><th>Diperbarui</th><th>Aksi</th></tr></thead><tbody id="cardsTable"></tbody></table></div></div>
            </section>

            <section class="section" id="section-lockers">
                <div class="intro"><div><div class="eyebrow">DEVICE MANAGEMENT</div><h1>Manajemen Loker</h1><p>Pasangkan setiap loker dengan perangkat ESP32 dan atur statusnya.</p></div></div>
                <div class="notice">ID perangkat di sini harus sama dengan <span class="mono">LOCKER_DEVICE_ID</span> pada backend dan <span class="mono">DEVICE_ID</span> pada sketch ESP32. Pada paket awal, satu API key hanya dikonfigurasi untuk satu perangkat uji.</div>
                <div class="grid-two">
                    <div class="panel"><div class="panel-head"><div><h2 id="lockerFormTitle">Tambah loker</h2><p>Contoh ID loker L01 dan perangkat ESP32_01.</p></div></div><div class="panel-body"><form id="lockerForm"><div class="form-grid">
                        <div class="field"><label for="lockerId">ID LOKER</label><input id="lockerId" required maxlength="40" placeholder="Contoh: L01"></div>
                        <div class="field"><label for="lockerDevice">ID PERANGKAT</label><input id="lockerDevice" required maxlength="60" placeholder="Contoh: ESP32_01"></div>
                        <div class="field full"><label class="check-row"><input id="lockerActive" type="checkbox" checked> Loker aktif</label></div>
                    </div><div class="form-actions"><button class="btn" type="submit" id="lockerSubmit">Simpan loker</button><button class="btn-light" type="button" id="lockerCancel" onclick="resetLockerForm()" hidden>Batal edit</button></div></form></div></div>
                    <div class="panel"><div class="panel-head"><div><h2>Catatan perangkat</h2><p>Aturan untuk pemetaan loker.</p></div></div><div class="panel-body"><p class="muted" style="line-height:1.8;font-size:12px">• Satu perangkat terhubung ke satu loker.<br>• Jika loker nonaktif, semua tap akan ditolak.<br>• Jika perangkat dipindah ke loker lain, pastikan firmware fisiknya juga sesuai.<br>• Perangkat harus terdaftar pada Firebase untuk dapat memverifikasi kartu.</p></div></div>
                </div>
                <div class="panel"><div class="panel-head"><div><h2>Daftar loker</h2><p id="lockerCount">Status loker dan perangkat.</p></div></div><div class="table-wrap"><table><thead><tr><th>ID loker</th><th>Pemilik</th><th>Perangkat</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="lockersTable"></tbody></table></div></div>
            </section>

            <section class="section" id="section-logs">
                <div class="intro"><div><div class="eyebrow">AUDIT TRAIL</div><h1>Log Akses</h1><p>Riwayat tap RFID yang diterima dari API verifikasi.</p></div><button class="btn" onclick="refreshData(true)">↻ &nbsp; Refresh log</button></div>
                <div class="notice">Log berisi hasil yang diputuskan backend. Jika ESP32 tidak dapat terhubung ke API, permintaan mungkin tidak sampai dan tidak tercatat di Firebase.</div>
                <div class="panel"><div class="panel-head"><div><h2>Riwayat akses</h2><p id="logCount">Log tersimpan di Firebase.</p></div><div class="toolbar"><select class="search" id="logFilter" aria-label="Filter hasil"><option value="all">Semua hasil</option><option value="granted">Diterima</option><option value="denied">Ditolak</option></select><input class="search" id="logSearch" placeholder="Cari UID / loker…"></div></div><div class="table-wrap"><table><thead><tr><th>Waktu</th><th>UID</th><th>ID loker</th><th>Perangkat</th><th>Hasil</th><th>Alasan</th></tr></thead><tbody id="logsTable"></tbody></table></div></div>
            </section>
            <div class="footer"><span>© {{ date('Y') }} BlueVault Smart Locker · Prototype</span><span>Black / White / Blue interface</span></div>
        </div>
    </main>
</div>
<div id="toast" class="toast" role="status" aria-live="polite"></div>
<script>
const urls = {
    data: @json(route('locker.data')),
    students: @json(url('/admin/students')),
    cards: @json(url('/admin/cards')),
    lockers: @json(url('/admin/lockers')),
    logout: @json(route('locker.logout')),
};
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let state = {students:{},cards:{},lockers:{},devices:{},accessLogs:{}};
let editingStudentId = null;
let editingLockerId = null;
let toastTimer = null;
const titles = {overview:'Dashboard overview',students:'Data siswa',cards:'Kartu RFID',lockers:'Manajemen loker',logs:'Log akses'};
const $ = (id) => document.getElementById(id);
const esc = (value) => String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
const entries = (obj) => Object.entries(obj || {}).filter(([key, value]) => value && typeof value === 'object');
const statusBadge = (active) => `<span class="badge ${active ? 'active' : 'inactive'}">${active ? 'Aktif' : 'Nonaktif'}</span>`;
const logBadge = (result) => `<span class="badge ${result === 'granted' ? 'active' : 'inactive'}">${result === 'granted' ? 'Diterima' : 'Ditolak'}</span>`;
const formatTime = (value) => { const n = Number(value || 0); if (!n) return '—'; const d = new Date(n); return Number.isNaN(d.getTime()) ? '—' : d.toLocaleString('id-ID',{dateStyle:'medium',timeStyle:'short'}); };
function showToast(message, isError=false){const t=$('toast');t.textContent=message;t.className='toast show'+(isError?' error':'');clearTimeout(toastTimer);toastTimer=setTimeout(()=>t.className='toast',3600);}
async function api(url, method='GET', body=null){
    const options={method,headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'}};
    if(body!==null){options.headers['Content-Type']='application/json';options.body=JSON.stringify(body);}
    const response=await fetch(url,options);
    const data=await response.json().catch(()=>({message:`Respons server tidak valid (HTTP ${response.status}).`}));
    if(!response.ok){let message=data.message||`Permintaan gagal (HTTP ${response.status}).`;if(data.errors){message=Object.values(data.errors).flat().join(' ');}throw new Error(message);}
    return data;
}
function showSection(name){document.querySelectorAll('.section').forEach(el=>el.classList.toggle('active',el.id===`section-${name}`));document.querySelectorAll('.nav button').forEach(el=>el.classList.toggle('active',el.dataset.section===name));$('topTitle').textContent=titles[name]||'Dashboard';window.scrollTo({top:0,behavior:'smooth'});}
document.querySelectorAll('.nav button').forEach(button=>button.addEventListener('click',()=>showSection(button.dataset.section)));
function lockerOptions(selected='', studentId=''){
    const options=['<option value="">Tanpa loker</option>'];
    for(const [id,l] of entries(state.lockers).sort(([a],[b])=>a.localeCompare(b))){
        const owner=l.studentId||'';const unavailable=owner && owner!==studentId;
        options.push(`<option value="${esc(id)}" ${id===selected?'selected':''} ${unavailable?'disabled':''}>${esc(id)}${owner?` · ${esc(state.students[owner]?.name||owner)}`:' · tersedia'}${l.active?'':' · nonaktif'}</option>`);
    }
    return options.join('');
}
function studentOptions(selected=''){
    const options=['<option value="">Pilih siswa</option>'];
    for(const [id,s] of entries(state.students).sort(([,a],[,b])=>String(a.name||'').localeCompare(String(b.name||'')))){
        options.push(`<option value="${esc(id)}" ${id===selected?'selected':''}>${esc(s.name||id)} · ${esc(id)}${s.active?'':' (nonaktif)'}</option>`);
    }
    return options.join('');
}
function render(){
    const students=entries(state.students), cards=entries(state.cards), lockers=entries(state.lockers), logs=entries(state.accessLogs).sort((a,b)=>Number(b[1].timestamp||0)-Number(a[1].timestamp||0));
    $('statStudents').textContent=students.length;
    $('statCards').textContent=cards.filter(([,c])=>c.active===true).length;
    $('statLockers').textContent=lockers.filter(([,l])=>l.active===true).length;
    $('statDenied').textContent=logs.filter(([,l])=>l.result==='denied').length;
    $('studentCount').textContent=`${students.length} siswa tersimpan`;
    $('cardCount').textContent=`${cards.length} UID tersimpan`;
    $('lockerCount').textContent=`${lockers.length} loker terdaftar`;
    $('logCount').textContent=`${logs.length} log termuat`;
    $('studentLocker').innerHTML=lockerOptions($('studentLocker').value,editingStudentId||'');
    $('cardStudent').innerHTML=studentOptions($('cardStudent').value);

    const sq=($('studentSearch').value||'').toLowerCase();
    const studentRows=students.filter(([id,s])=>`${id} ${s.name||''} ${s.class||''} ${s.lockerId||''}`.toLowerCase().includes(sq));
    $('studentsTable').innerHTML=studentRows.length?studentRows.map(([id,s])=>`<tr><td><div class="identity"><div class="mini-avatar">${esc((s.name||id).trim().slice(0,1).toUpperCase())}</div><div><strong>${esc(s.name||'—')}</strong><span>${esc(id)}</span></div></div></td><td>${esc(s.class||'—')}</td><td>${s.lockerId?`<span class="mono">${esc(s.lockerId)}</span>`:'<span class="muted">Belum ditetapkan</span>'}</td><td>${statusBadge(s.active===true)}</td><td><button class="btn-light" onclick="editStudent('${esc(id)}')">Edit</button></td></tr>`).join(''):'<tr><td colspan="5" class="empty"><strong>Belum ada data siswa</strong>Tambahkan siswa melalui formulir.</td></tr>';

    const cq=($('cardSearch').value||'').toLowerCase();
    const cardRows=cards.filter(([uid,c])=>`${uid} ${c.studentId||''} ${state.students[c.studentId]?.name||''}`.toLowerCase().includes(cq));
    $('cardsTable').innerHTML=cardRows.length?cardRows.map(([uid,c])=>{const s=state.students[c.studentId]||{};return `<tr><td class="mono">${esc(uid)}</td><td><div class="identity"><div class="mini-avatar">${esc((s.name||c.studentId||'?').slice(0,1).toUpperCase())}</div><div><strong>${esc(s.name||'Siswa tidak ditemukan')}</strong><span>${esc(c.studentId||'—')}</span></div></div></td><td>${esc(s.lockerId||'—')}</td><td>${statusBadge(c.active===true)}</td><td>${esc(formatTime(c.updatedAt))}</td><td><div class="actions"><button class="btn-light" onclick="transferCard('${esc(uid)}')">Pindah pemilik</button><button class="${c.active?'btn-danger':'btn-light'}" onclick="setCardStatus('${esc(uid)}',${!c.active})">${c.active?'Nonaktifkan':'Aktifkan'}</button></div></td></tr>`;}).join(''):'<tr><td colspan="6" class="empty"><strong>Belum ada kartu RFID</strong>Daftarkan UID dari menu ini.</td></tr>';

    $('lockersTable').innerHTML=lockers.length?lockers.sort(([a],[b])=>a.localeCompare(b)).map(([id,l])=>{const s=state.students[l.studentId]||{};return `<tr><td><strong class="mono">${esc(id)}</strong></td><td>${s.name?`${esc(s.name)} <span class="muted">(${esc(l.studentId)})</span>`:'<span class="muted">Belum ditetapkan</span>'}</td><td class="mono">${esc(l.deviceId||'—')}</td><td>${statusBadge(l.active===true)}</td><td><button class="btn-light" onclick="editLocker('${esc(id)}')">Edit</button></td></tr>`;}).join(''):'<tr><td colspan="5" class="empty"><strong>Belum ada loker</strong>Tambahkan loker dan perangkat ESP32.</td></tr>';

    const recent=logs.slice(0,6);
    $('recentLogs').innerHTML=recent.length?recent.map(([,l])=>`<tr><td>${esc(formatTime(l.timestamp))}</td><td class="mono">${esc(l.uid||'—')}</td><td>${esc(l.lockerId||'—')}</td><td class="mono">${esc(l.deviceId||'—')}</td><td>${logBadge(l.result)}</td></tr>`).join(''):'<tr><td colspan="5" class="empty">Belum ada aktivitas akses.</td></tr>';

    const filter=$('logFilter').value, lq=($('logSearch').value||'').toLowerCase();
    const shown=logs.filter(([,l])=>(filter==='all'||l.result===filter)&&`${l.uid||''} ${l.lockerId||''} ${l.deviceId||''} ${l.reason||''}`.toLowerCase().includes(lq)).slice(0,250);
    $('logsTable').innerHTML=shown.length?shown.map(([,l])=>`<tr><td>${esc(formatTime(l.timestamp))}</td><td class="mono">${esc(l.uid||'—')}</td><td>${esc(l.lockerId||'—')}</td><td class="mono">${esc(l.deviceId||'—')}</td><td>${logBadge(l.result)}</td><td>${esc((l.reason||'—').replaceAll('_',' '))}</td></tr>`).join(''):'<tr><td colspan="6" class="empty">Tidak ada log yang sesuai.</td></tr>';
}
async function refreshData(notify=false){try{const response=await api(urls.data);state={students:response.students||{},cards:response.cards||{},lockers:response.lockers||{},devices:response.devices||{},accessLogs:response.accessLogs||{}};render();if(notify)showToast('Data berhasil diperbarui dari Firebase.');}catch(e){showToast(e.message,true);}}
function resetStudentForm(){editingStudentId=null;$('studentForm').reset();$('studentActive').checked=true;$('studentId').disabled=false;$('studentFormTitle').textContent='Tambah siswa';$('studentSubmit').textContent='Simpan siswa';$('studentCancel').hidden=true;render();}
function editStudent(id){const s=state.students[id];if(!s)return;editingStudentId=id;$('studentId').value=id;$('studentId').disabled=true;$('studentName').value=s.name||'';$('studentClass').value=s.class||'';$('studentActive').checked=s.active===true;$('studentLocker').innerHTML=lockerOptions(s.lockerId||'',id);$('studentLocker').value=s.lockerId||'';$('studentFormTitle').textContent=`Edit siswa · ${id}`;$('studentSubmit').textContent='Simpan perubahan';$('studentCancel').hidden=false;showSection('students');$('studentName').focus();}
$('studentForm').addEventListener('submit',async e=>{e.preventDefault();const payload={id:$('studentId').value.trim(),name:$('studentName').value.trim(),class:$('studentClass').value.trim(),lockerId:$('studentLocker').value,active:$('studentActive').checked};try{const isEdit=Boolean(editingStudentId);const url=isEdit?`${urls.students}/${encodeURIComponent(editingStudentId)}`:urls.students;const result=await api(url,isEdit?'PUT':'POST',payload);showToast(result.message||'Siswa berhasil disimpan.');resetStudentForm();await refreshData();}catch(err){showToast(err.message,true);}});
function resetCardForm(){ $('cardForm').reset();$('cardActive').checked=true; }
$('cardForm').addEventListener('submit',async e=>{e.preventDefault();const payload={uid:$('cardUid').value.trim(),studentId:$('cardStudent').value,active:$('cardActive').checked};try{const result=await api(urls.cards,'POST',payload);showToast(result.message||'Kartu berhasil disimpan.');resetCardForm();await refreshData();}catch(err){showToast(err.message,true);}});
function transferCard(uid){const card=state.cards[uid]||{};$('cardUid').value=uid;$('cardStudent').value=card.studentId||'';$('cardActive').checked=card.active===true;showSection('cards');$('cardUid').focus();}
async function setCardStatus(uid,active){try{const result=await api(`${urls.cards}/${encodeURIComponent(uid)}`,'PATCH',{active});showToast(result.message||'Status kartu diperbarui.');await refreshData();}catch(err){showToast(err.message,true);}}
function resetLockerForm(){editingLockerId=null;$('lockerForm').reset();$('lockerActive').checked=true;$('lockerId').disabled=false;$('lockerFormTitle').textContent='Tambah loker';$('lockerSubmit').textContent='Simpan loker';$('lockerCancel').hidden=true;}
function editLocker(id){const l=state.lockers[id];if(!l)return;editingLockerId=id;$('lockerId').value=id;$('lockerId').disabled=true;$('lockerDevice').value=l.deviceId||'';$('lockerActive').checked=l.active===true;$('lockerFormTitle').textContent=`Edit loker · ${id}`;$('lockerSubmit').textContent='Simpan perubahan';$('lockerCancel').hidden=false;showSection('lockers');$('lockerDevice').focus();}
$('lockerForm').addEventListener('submit',async e=>{e.preventDefault();const payload={id:$('lockerId').value.trim(),deviceId:$('lockerDevice').value.trim(),active:$('lockerActive').checked};try{const isEdit=Boolean(editingLockerId);const url=isEdit?`${urls.lockers}/${encodeURIComponent(editingLockerId)}`:urls.lockers;const result=await api(url,isEdit?'PUT':'POST',payload);showToast(result.message||'Loker berhasil disimpan.');resetLockerForm();await refreshData();}catch(err){showToast(err.message,true);}});
['studentSearch','cardSearch','logSearch','logFilter'].forEach(id=>$(id).addEventListener('input',render));
refreshData();
</script>
</body>
</html>
