<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    if (getUserRole() === 'student') redirect('student_dashboard.php');
    if (getUserRole() === 'teacher' || getUserRole() === 'admin') redirect('teacher_dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        if ($user['role'] === 'student') {
            $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['student_id'] = $student['id'];
            redirect('student_dashboard.php');
        } else {
            redirect('teacher_dashboard.php');
        }
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Result System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="theme-toggle-container">
        <button class="theme-toggle" id="themeToggle">
            <i class="fas fa-moon"></i>
            <i class="fas fa-sun"></i>
        </button>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1>Student Result System</h1>
                <p>Sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>
            
            <div class="login-footer">
                <div class="demo-credentials">
                    <h4>Demo Mode</h4>
                    <p>Ask your teacher for login credentials</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
