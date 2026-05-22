<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified</title>

    <style>

        body{
            margin:0;
            padding:0;
            background:#f3f4f6;
            font-family:Arial,sans-serif;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }

        .card{
            background:white;
            width:400px;
            padding:40px;
            border-radius:20px;
            text-align:center;
            box-shadow:0 10px 30px rgba(0,0,0,0.1);
        }

        .check{
            width:100px;
            height:100px;
            background:#0000A3;
            border-radius:50%;
            margin:auto;
            display:flex;
            align-items:center;
            justify-content:center;
            color:white;
            font-size:50px;
        }

        h1{
            margin-top:25px;
            color:#111827;
        }

        p{
            color:#6b7280;
            line-height:1.7;
        }

        .btn{
            display:inline-block;
            margin-top:25px;
            padding:14px 24px;
            background:#0000A3;
            color:white;
            text-decoration:none;
            border-radius:10px;
            font-weight:bold;
        }

    </style>
</head>
<body>

<div class="card">

    <div class="logos">
        <img src="/images/KOSKAKI.png" alt="Koskaki Logo" width="100">
    </div>

    <div class="check">
        ✓
    </div>

    <h1>Email Berhasil Diverifikasi</h1>

    <p>
        Terima kasih sudah memverifikasi email akun kamu.
        Sekarang akun sudah aktif dan siap digunakan.
    </p>

    <a class="btn" href="koskaki://verified">
        Buka Aplikasi
    </a>

</div>

</body>
</html>
