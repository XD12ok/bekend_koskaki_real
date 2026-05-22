<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Email</title>
</head>
<body style="margin:0; padding:0; background:#f4f4f4; font-family:Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:40px 20px;">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:16px; overflow:hidden;">

                    <!-- HEADER -->
                    <tr>
                        <td align="center"
                            style="background:#2563eb; padding:30px; color:white;">

                            <h1 style="margin:0; font-size:28px;">
                                KosKaKi
                            </h1>

                            <p style="margin-top:10px; opacity:0.9;">
                                Verifikasi Email Akun Kamu
                            </p>
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td style="padding:40px; color:#333333;">

                            <h2 style="margin-top:0;">
                                Halo, {{ $name }} 👋
                            </h2>

                            <p style="line-height:1.8;">
                                Terima kasih telah mendaftar di aplikasi KosKaKi.
                                Klik tombol di bawah untuk memverifikasi email akun kamu.
                            </p>

                            <div style="text-align:center; margin:40px 0;">

                                <a href="{{ $url }}"
                                   style="
                                        background:#2563eb;
                                        color:white;
                                        text-decoration:none;
                                        padding:14px 28px;
                                        border-radius:10px;
                                        display:inline-block;
                                        font-weight:bold;
                                   ">
                                    Verifikasi Email
                                </a>

                            </div>

                            <p style="line-height:1.8;">
                                Link verifikasi ini akan expired dalam 60 menit.
                            </p>

                            <p style="line-height:1.8;">
                                Jika kamu tidak merasa membuat akun ini,
                                abaikan email ini.
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center"
                            style="
                                background:#f8fafc;
                                padding:25px;
                                color:#888;
                                font-size:13px;
                            ">

                            © {{ date('Y') }} KosKaKi. All rights reserved.

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
