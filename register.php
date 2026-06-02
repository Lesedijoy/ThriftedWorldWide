<?php
session_start();
include 'includes/db.php';

$error   = '';
$success = '';

if (isset($_POST['register'])) {
    $first    = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last     = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $province = mysqli_real_escape_string($conn, trim($_POST['province']));
    $pw       = $_POST['password'];
    $pw2      = $_POST['confirm_password'];

    // Validation
    if (empty($first) || empty($last) || empty($username) || empty($email) || empty($pw)) {
        $error = 'Please fill in all required fields.';
    } elseif ($pw !== $pw2) {
        $error = 'Passwords do not match.';
    } elseif (strlen($pw) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        // Check for duplicate email / username
        $check = mysqli_query($conn,
            "SELECT id FROM users WHERE email='$email' OR username='$username' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $error = 'That email or username is already taken.';
        } else {
            $hashed = password_hash($pw, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users
                    (first_name, last_name, username, email, phone, province, password)
                    VALUES
                    ('$first','$last','$username','$email','$phone','$province','$hashed')";
            if (mysqli_query($conn, $sql)) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — ThriftedWorldWide</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
  
    <?php include 'includes/nav.php'; ?>
   

<div class="auth-page">
    <div class="auth-box-wide">
        <div class="auth-header">
            <a href="index.php" class="auth-logo">Thrifted<span>Worldwide</span></a>
            <h1 class="auth-title">Create your <em>account</em></h1>
            <p class="auth-subtitle">Join thousands of thrifters across South Africa</p>
        </div>

        <div class="auth-card">
            <?php if ($error): ?>
                <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="two-col-auth">
                    <div class="auth-group">
                        <label for="firstName">First name *</label>
                        <input type="text" id="firstName" name="first_name"
                               placeholder="Lesedi"
                               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="auth-group">
                        <label for="lastName">Last name *</label>
                        <input type="text" id="lastName" name="last_name"
                               placeholder="Dlamini"
                               value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="auth-group" style="margin-top:1rem;">
                    <label for="username">Username *</label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">@</span>
                        <input type="text" id="username" name="username"
                               placeholder="thrift_lesedi"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               oninput="checkUsername(this)" required>
                    </div>
                    <div class="field-hint" id="usernameHint">Choose a unique username</div>
                </div>

                <div class="auth-group" style="margin-top:1rem;">
                    <label for="regEmail">Email address *</label>
                    <input type="email" id="regEmail" name="email"
                           placeholder="you@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="auth-group" style="margin-top:1rem;">
                    <label for="phone">Phone number</label>
                    <input type="tel" id="phone" name="phone"
                           placeholder="+27 82 000 0000"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>

                <div class="auth-group" style="margin-top:1rem;">
                    <label for="province">Province</label>
                    <select id="province" name="province">
                        <option value="">Select your province</option>
                        <?php
                        $provinces = ['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape',
                                      'Limpopo','Mpumalanga','North West','Free State','Northern Cape'];
                        foreach ($provinces as $p) {
                            $sel = (($_POST['province'] ?? '') === $p) ? 'selected' : '';
                            echo "<option value=\"$p\" $sel>$p</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="auth-group" style="margin-top:1rem;">
                    <label for="regPass">Password *</label>
                    <div class="pass-wrap">
                        <input type="password" id="regPass" name="password"
                               placeholder="Min 8 characters"
                               oninput="checkStrength(this.value)" required>
                        <span class="pass-toggle" onclick="togglePass('regPass', this)">👁</span>
                    </div>
                    <div class="strength-bars">
                        <div class="str-bar" id="s1"></div>
                        <div class="str-bar" id="s2"></div>
                        <div class="str-bar" id="s3"></div>
                        <div class="str-bar" id="s4"></div>
                    </div>
                    <div class="field-hint" id="strLabel">Enter a password</div>
                </div>

                <div class="auth-group" style="margin-top:1rem;">
                    <label for="confirmPass">Confirm password *</label>
                    <input type="password" id="confirmPass" name="confirm_password"
                           placeholder="Repeat your password"
                           oninput="checkMatch()" required>
                    <div class="field-hint" id="matchHint"></div>
                </div>

                <label class="check-label-terms" style="margin-top:0.75rem;">
                    <input type="checkbox" id="agreeTerms" required>
                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                </label>

                <button class="btn-auth-accent" type="submit" name="register" style="margin-top:1rem;">
                    Create account
                </button>
            </form>

            <button class="btn-auth-google" style="margin-top:0.5rem;">🌐 Sign up with Google</button>

            <div class="auth-switch" style="margin-top:0.75rem;">
                Already have an account? <a href="login.php">Sign in</a>
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

function checkStrength(v) {
    const bars = ['s1','s2','s3','s4'];
    const label = document.getElementById('strLabel');
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const colors = ['#E24B4A','#F5A623','#4CAF50','#27AE60'];
    const labels = ['Too short','Weak','Good','Strong'];
    bars.forEach((id, i) => {
        document.getElementById(id).style.background = i < score ? colors[score-1] : 'var(--warm)';
    });
    label.textContent = v.length === 0 ? 'Enter a password' : labels[score-1] || labels[0];
}

function checkMatch() {
    const p1 = document.getElementById('regPass').value;
    const p2 = document.getElementById('confirmPass').value;
    const hint = document.getElementById('matchHint');
    if (!p2) { hint.textContent = ''; return; }
    hint.textContent = p1 === p2 ? '✓ Passwords match' : '✗ Passwords do not match';
    hint.className = 'field-hint ' + (p1 === p2 ? 'success' : 'error');
}

function checkUsername(el) {
    const hint = document.getElementById('usernameHint');
    const val = el.value.trim();
    if (val.length < 3) { hint.textContent = 'Username must be at least 3 characters'; hint.className = 'field-hint error'; return; }
    fetch('check-username.php?username=' + encodeURIComponent(val))
        .then(r => r.json())
        .then(data => {
            hint.textContent = data.available ? '✓ Username available' : '✗ Username taken';
            hint.className = 'field-hint ' + (data.available ? 'success' : 'error');
        });
}

const cart = JSON.parse(localStorage.getItem('tww_cart') || '[]');
document.getElementById('cartCount').textContent = cart.length;
</script>
</body>
</html>