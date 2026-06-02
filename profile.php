<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$uid = (int)$_SESSION['user_id'];

$success = '';
$error   = '';

// Load user
$r    = mysqli_query($conn, "SELECT * FROM users WHERE id=$uid LIMIT 1");
$user = mysqli_fetch_assoc($r);

// Handle profile update
if (isset($_POST['update_profile'])) {
    $first    = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last     = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $province = mysqli_real_escape_string($conn, trim($_POST['province']));
    $avatarSql= '';

    // Handle avatar upload
    if (!empty($_FILES['avatar']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed) && $_FILES['avatar']['size'] < 3 * 1024 * 1024) {
            $fname   = 'avatar_' . $uid . '_' . time() . '.' . $ext;
            $dest    = 'uploads/avatars/' . $fname;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                // Remove old avatar
                if (!empty($user['avatar']) && file_exists('uploads/avatars/'.$user['avatar'])) {
                    unlink('uploads/avatars/'.$user['avatar']);
                }
                $avatarSql = ", avatar='$fname'";
            }
        }
    }

    // Password change (optional)
    $pwSql = '';
    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif (!password_verify($_POST['current_password'], $user['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $pwSql  = ", password='$hashed'";
        }
    }

    if (!$error) {
        mysqli_query($conn,
            "UPDATE users SET first_name='$first', last_name='$last',
             phone='$phone', province='$province' $avatarSql $pwSql
             WHERE id=$uid");
        $_SESSION['full_name'] = "$first $last";
        $success = 'Profile updated successfully!';
        // Reload user data
        $r    = mysqli_query($conn, "SELECT * FROM users WHERE id=$uid LIMIT 1");
        $user = mysqli_fetch_assoc($r);
    }
}

$provinces = ['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Limpopo','Mpumalanga','North West','Free State','Northern Cape'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile — ThriftedWorldWide</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
.profile-wrap { max-width: 680px; margin: 0 auto; padding: 3rem 2rem; }
.profile-heading { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 600;
    color: #1A1A1A; margin-bottom: 0.25rem; }
.profile-heading em { font-style: italic; color: #C4622D; }
.profile-sub { font-size: 0.875rem; color: #6B6560; margin-bottom: 2rem; }

.profile-card { background: #fff; border: 1px solid rgba(0,0,0,0.08); border-radius: 20px; padding: 2rem; margin-bottom: 1.5rem; }
.card-section-title { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
    color: #6B6560; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(0,0,0,0.07); }

/* Avatar picker */
.avatar-picker { display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem; }
.profile-avatar-preview { width: 80px; height: 80px; border-radius: 50%; background: #EDE6DC;
    color: #C4622D; font-size: 1.6rem; font-weight: 700; display: flex; align-items: center;
    justify-content: center; object-fit: cover; flex-shrink: 0; border: 3px solid #F7F3EE; }
.avatar-upload-label { background: #F7F3EE; color: #1A1A1A; padding: 9px 18px; border-radius: 100px;
    font-size: 0.82rem; font-weight: 500; cursor: pointer; border: 1px solid rgba(0,0,0,0.1);
    transition: background .2s; }
.avatar-upload-label:hover { background: #EDE6DC; }
.avatar-hint { font-size: 0.75rem; color: #6B6560; margin-top: 4px; }

/* Form groups */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-group { display: flex; flex-direction: column; margin-bottom: 1rem; }
.form-group:last-child { margin-bottom: 0; }
.form-group label { font-size: 0.8rem; font-weight: 500; color: #6B6560; margin-bottom: 6px; }
.form-group input, .form-group select {
    font-family: 'DM Sans', sans-serif; padding: 11px 16px;
    border: 1.5px solid rgba(0,0,0,0.15); border-radius: 10px; font-size: 0.9rem;
    background: #fff; color: #1A1A1A; outline: none; transition: border-color .2s; width: 100%;
}
.form-group input:focus, .form-group select:focus { border-color: #C4622D; }
.form-group input[readonly] { background: #F7F3EE; color: #6B6560; cursor: not-allowed; }

.btn-save { background: #1A1A1A; color: #fff; padding: 13px 32px; border-radius: 100px;
    border: none; font-size: 0.95rem; font-weight: 500; cursor: pointer;
    font-family: 'DM Sans', sans-serif; transition: background .2s; }
.btn-save:hover { background: #C4622D; }

.alert-success { background: #E8F5E9; color: #2E7D32; padding: 0.85rem 1rem;
    border-radius: 10px; font-size: 0.88rem; margin-bottom: 1rem; }
.alert-error { background: #FFEBEE; color: #C62828; padding: 0.85rem 1rem;
    border-radius: 10px; font-size: 0.88rem; margin-bottom: 1rem; }

@media(max-width:600px){ .form-row{grid-template-columns:1fr;} }
</style>
</head>
<body>
<?php include 'includes/nav.php'; ?>

<div class="profile-wrap">
    <a href="dashboard.php" style="font-size:0.82rem;color:#6B6560;text-decoration:none;display:inline-block;margin-bottom:1.5rem;">← Back to dashboard</a>

    <h1 class="profile-heading">Edit your <em>profile</em></h1>
    <p class="profile-sub">Update your personal info, photo, and password.</p>

    <?php if ($success): ?><div class="alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" action="profile.php" enctype="multipart/form-data">

        <!-- PROFILE PHOTO -->
        <div class="profile-card">
            <div class="card-section-title">Profile photo</div>
            <div class="avatar-picker">
                <?php if (!empty($user['avatar']) && file_exists('uploads/avatars/'.$user['avatar'])): ?>
                    <img src="uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>"
                         class="profile-avatar-preview" id="avatarPreview" alt="Avatar">
                <?php else: ?>
                    <div class="profile-avatar-preview" id="avatarPreviewDiv">
                        <?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <label class="avatar-upload-label" for="avatar">📷 Upload new photo</label>
                    <input type="file" name="avatar" id="avatar" accept="image/*"
                           style="display:none;" onchange="previewAvatar(this)">
                    <div class="avatar-hint">JPG, PNG or WebP · Max 3 MB</div>
                </div>
            </div>
        </div>

        <!-- PERSONAL INFO -->
        <div class="profile-card">
            <div class="card-section-title">Personal information</div>
            <div class="form-row">
                <div class="form-group">
                    <label>First name</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Last name</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Username (cannot be changed)</label>
                <input type="text" value="@<?= htmlspecialchars($user['username']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Email (cannot be changed)</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Phone number</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                           placeholder="+27 82 000 0000">
                </div>
                <div class="form-group">
                    <label>Province</label>
                    <select name="province">
                        <?php foreach ($provinces as $p): ?>
                            <option value="<?= $p ?>" <?= ($user['province']===$p)?'selected':'' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- PASSWORD CHANGE -->
        <div class="profile-card">
            <div class="card-section-title">Change password <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#6B6560;">(leave blank to keep current)</span></div>
            <div class="form-group">
                <label>Current password</label>
                <input type="password" name="current_password" placeholder="Enter current password">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>New password</label>
                    <input type="password" name="new_password" placeholder="Min 8 characters">
                </div>
                <div class="form-group">
                    <label>Confirm new password</label>
                    <input type="password" name="confirm_password" placeholder="Repeat new password">
                </div>
            </div>
        </div>

        <button type="submit" name="update_profile" class="btn-save">Save changes</button>
    </form>
</div>

<script>
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        let img = document.getElementById('avatarPreview');
        let div = document.getElementById('avatarPreviewDiv');
        if (div) {
            div.outerHTML = `<img id="avatarPreview" class="profile-avatar-preview" src="${e.target.result}" alt="Preview">`;
        } else if (img) {
            img.src = e.target.result;
        }
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
</body>
</html>