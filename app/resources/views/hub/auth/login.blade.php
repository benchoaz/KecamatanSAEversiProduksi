<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROBOLINGGO SAE | Dashboard Gateway</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --primary-accent: #3498db;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        .background {
            background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1588666309990-d68f08e3d4a6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80'); /* Bromo-like background */
            background-size: cover;
            background-position: center;
            height: 100%;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: absolute;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            width: 400px;
            text-align: center;
            color: white;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .logo-area img {
            width: 80px;
            margin-bottom: 20px;
        }

        h2 {
            margin: 0;
            font-size: 24px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.7;
        }

        input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: white;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--primary-accent);
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #2c3e50;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        button:hover {
            background: #1a252f;
            transform: translateY(-2px);
        }

        .footer-text {
            margin-top: 20px;
            font-size: 12px;
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <div class="background">
        <div class="login-card">
            <div class="logo-area">
                <img src="https://upload.wikimedia.org/wikipedia/commons/f/fb/Lambang_Kabupaten_Probolinggo.png" alt="Logo Probolinggo">
            </div>
            <h2>PROBOLINGGO SAE</h2>
            <div class="subtitle">DASHBOARD GATEWAY HUB</div>
            
            <form action="{{ route('hub.login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Username / NIK" required>
                </div>
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit">Masuk ke Sistem</button>
            </form>

            <div class="footer-text">
                &copy; 2026 Pemerintah Kabupaten Probolinggo
            </div>
        </div>
    </div>
</body>
</html>
