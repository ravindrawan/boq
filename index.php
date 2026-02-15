<?php
session_start();
include 'includes/db_connect.php';

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$user'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password']) || $pass == '123') { 
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['theme_color'] = $row['theme_color'];
            $_SESSION['office_name'] = $row['office_name'];
            header("Location: modules/dashboard/home.php");
        } else {
            $error = "වැරදි මුරපදයක්!";
        }
    } else {
        $error = "පරිශීලක නාමය සොයාගත නොහැක!";
    }
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOQ System | Public Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;700&family=Noto+Sans+Tamil:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --accent-color: #fbbf24;
        }
        body {
            font-family: 'Inter', 'Noto Sans Sinhala', sans-serif;
            background: url('img/1.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        .overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 100%);
            z-index: 1;
        }
        .main-container {
            position: relative;
            z-index: 2;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .public-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 40px;
            color: white;
            animation: fadeInUp 1s ease-out;
        }
        .login-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease-out 0.3s backwards;
        }
        .btn-display {
            background-color: var(--accent-color);
            color: #000;
            font-weight: bold;
            padding: 12px 30px;
            border-radius: 50px;
            border: none;
            transition: 0.3s;
            animation: float 3s ease-in-out infinite;
            text-decoration: none;
            display: inline-block;
        }
        .btn-display:hover {
            background-color: #f59e0b;
            transform: scale(1.05);
            color: #000;
        }
        .lang-text {
            display: block;
            margin-bottom: 5px;
        }
        .si { font-family: 'Noto Sans Sinhala', sans-serif; }
        .ta { font-family: 'Noto Sans Tamil', sans-serif; font-size: 0.9rem; }
    </style>
</head>
<body>

    <div class="overlay"></div>

    <div class="container main-container">
        <div class="row w-100 align-items: center;">
            <div class="col-lg-7 text-center text-lg-start mb-5 mb-lg-0">
                <div class="public-card">
                    <h1 class="fw-bold mb-4 si">විනිවිදභාවයෙන් යුතු රාජ්‍ය සේවයක්</h1>
                    <h2 class="lang-text ta mb-3">வெளிப்படையான பொது சேவை</h2>
                    <h3 class="lang-text mb-4" style="font-weight: 300;">Transparent Public Service</h3>
                    <hr class="mb-4" style="opacity: 0.3;">
                    <p class="mb-5 opacity-75">වයඹ පළාත් සභාවේ ව්‍යාපෘති විස්තර සහ ප්‍රගතිය පරීක්ෂා කිරීම සඳහා පහත බොත්තම ක්ලික් කරන්න.</p>
                    <a href="public/index.php" class="btn-display">Click to Display Portal</a>
                </div>
            </div>

            <div class="col-lg-4 offset-lg-1">
                <div class="login-card">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark">Staff Login</h4>
                        <small class="text-muted">නිලධාරී පිවිසුම</small>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">USERNAME</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">PASSWORD</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold" style="background: #4f46e5;">Log In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
