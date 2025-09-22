<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password OTP</title>
    <style>
        /* Style for the email */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #333;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        h2 {
            color: #4CAF50;
        }
        .otp {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .footer {
            font-size: 12px;
            color: #aaa;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password OTP</h2>
        <p>Halo,</p>
        <p>Untuk mengatur ulang password Anda, masukkan kode OTP berikut:</p>
        <p class="otp">{{ $otp }}</p>
        <p class="footer">Jika Anda tidak meminta untuk mengatur ulang password, abaikan pesan ini.</p>
    </div>
</body>
</html>
