<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SARCH32 - Login</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #2c3e50; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); width: 350px; }
        h2 { text-align: center; color: #333; margin-bottom: 1.5rem; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; background: #0078d4; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background: #005a9e; }
        .error { color: red; text-align: center; font-size: 14px; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Project Login</h2>
    
    <form action="login_process.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>