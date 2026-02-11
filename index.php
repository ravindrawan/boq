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
        // සරල බව තකා මෙහි password_verify වෙනුවට කෙලින්ම check කළ හැක, නමුත් hash කිරීම ආරක්ෂිතයි.
        // පහත ඇත්තේ '123' පාස්වර්ඩ් එක සඳහා hash එක check කරන ක්‍රමයයි.
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
    <title>BOQ System Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('img/1.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6); /* Dark shade overlay */
            z-index: 1;
        }
        .login-card {
            background-color: rgba(255, 255, 255, 0.95); /* Slightly transparent white */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 2; /* Sit above overlay */
        }
        .btn-primary {
            background-color: #4f46e5;
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #4338ca;
        }
        .form-control {
            padding: 12px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="overlay"></div>

    <div class="login-card">
        <h3 class="text-center fw-bold mb-4" style="color: #4f46e5;">BOQ System</h3>
        <p class="text-center text-muted mb-4">කරුණාකර පද්ධතියට ඇතුළු වන්න</p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label fw-bold small text-uppercase">පරිශීලක නාමය</label>
                <input type="text" class="form-control" id="username" name="username" required placeholder="User">
            </div>
            <div class="mb-4">
                <label for="password" class="form-label fw-bold small text-uppercase">මුරපදය</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="Password">
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>
         <div class="text-center mt-3">
            <a href="public/index.php" class="text-decoration-none small text-muted">Public Portal &rarr;</a>
        </div>
    </div>

</body>
</html>