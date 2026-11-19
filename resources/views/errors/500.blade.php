<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Terjadi Kesalahan</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f3f4f6; color: #1f2937; font-family: ui-sans-serif, system-ui, sans-serif; }
        .card { max-width: 28rem; margin: 1.5rem; padding: 2rem; background: #fff; border: 1px solid #e5e7eb; border-radius: .75rem; box-shadow: 0 1px 2px rgb(0 0 0 / .05); }
        .code { font-size: 3rem; font-weight: 800; color: #b91c1c; line-height: 1; }
        h1 { margin: 1rem 0 .5rem; font-size: 1.35rem; }
        p { margin: 0 0 1.5rem; color: #4b5563; line-height: 1.6; }
        a { display: inline-block; padding: .6rem 1rem; border-radius: .5rem; background: #1d4ed8; color: #fff; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <main class="card">
        <div class="code">500</div>
        <h1>Terjadi Kesalahan</h1>
        <p>Server tidak dapat memproses permintaan Anda. Coba lagi beberapa saat lagi.</p>
        <a href="{{ url('/') }}">Kembali ke Beranda</a>
    </main>
</body>
</html>
