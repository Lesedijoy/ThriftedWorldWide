<?php
session_start();
include 'includes/db.php';
 
$error = '';
 
// If already logged in, redirect
if (!empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
 
    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "SELECT id, first_name, role, password FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
 
        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id']   = $row['id'];
                $_SESSION['role']      = $row['role'];
                $_SESSION['first_name'] = $row['first_name'];
 
                if ($row['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = 'Incorrect email or password.';
            }
        } else {
            $error = 'No account found with that email.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login-ThriftedWorldWide</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

</head>
<body>
    
    <?php include 'includes/nav.php'; ?>
   

<div class="auth-page">
  <div class="auth-box">
 
    <div class="auth-header">
      <a href="index.php" class="auth-logo">Thrifted<span>Worldwide</span></a>
      <h1 class="auth-title">Welcome <em>back</em></h1>
      <p class="auth-subtitle">Sign in to your account to continue</p>
    </div>
 
    <div class="auth-card">
  <?php if ($error): ?>
                <div style="background:#FEE8E8;color:#C0392B;padding:12px 16px;border-radius:10px;font-size:0.85rem;">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
 
            <form method="POST" action="login.php">
 
                <div class="auth-group" style="margin-bottom:1.1rem;">
                    <label for="loginEmail">Email address</label>
                    <input type="email" id="loginEmail" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="you@example.com" required>
                </div>
 
                <div class="auth-group" style="margin-bottom:1.1rem;">
                    <div class="label-row">
                        <label for="loginPass">Password</label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>
                    <div class="pass-wrap">
                        <input type="password" id="loginPass" name="password"
                               placeholder="Enter your password" required>
                        <span class="pass-toggle" onclick="togglePass('loginPass', this)">👁</span>
                    </div>
                </div>
 
                <label class="check-label-auth" style="margin-bottom:0.5rem;">
                    <input type="checkbox" name="remember"> Remember me
                </label>
 
               <button class="btn-auth-primary" type="submit" name="login" style="margin-top:1rem;">
                    Sign in
                </button>
            </form>
 
            <div class="auth-divider" style="margin-top:1rem;">— or continue with —</div>
            <button class="btn-auth-google">🌐 Continue with Google</button>
 
            <div class="auth-switch" style="margin-top:0.75rem;">
                Don't have an account? <a href="register.php">Sign up free</a>
            </div>
        </div>
    </div>
</div>
 
<script>
function togglePass(id, el) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    el.textContent = input.type === 'password' ? '👁' : '🙈';
}
</script>
</body>
</html> 

      