<?php
require_once __DIR__ . '/../includes/db_connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create_user_type') {
            $name = trim($_POST['type_name'] ?? '');
            if ($name !== '') {
                $stmt = $pdo->prepare('INSERT INTO user_types (name) VALUES (?)');
                $stmt->execute([$name]);
                $success_message = 'User type created successfully!';
            } else {
                $error_message = 'Please enter a valid user type name.';
            }
        }

        if ($_POST['action'] === 'create_user') {
            $email = trim($_POST['email'] ?? '');
            $userTypeId = (int)($_POST['user_type_id'] ?? 0);

            if ($email !== '' && $userTypeId > 0) {
                $typeStmt = $pdo->prepare('SELECT name FROM user_types WHERE id = ?');
                $typeStmt->execute([$userTypeId]);
                $typeRow = $typeStmt->fetch();
                $typeName = $typeRow ? $typeRow['name'] : 'User';

                $password = bin2hex(random_bytes(6));
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                $roleMap = [
                    'Admin' => 'admin',
                    'Doctor' => 'doctor',
                    'Patient' => 'patient'
                ];
                $role = $roleMap[$typeName] ?? 'patient';

                $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, username, password_hash, user_type_id, force_password_change, created_by)
                                       VALUES (?,?,?,?,?,?,?,?,?)');
                $displayName = strstr($email, '@', true) ?: $email;
                $stmt->execute([
                    $displayName,
                    $email,
                    $passwordHash,
                    $role,
                    $email,
                    $passwordHash,
                    $userTypeId,
                    1,
                    $_SESSION['user_id']
                ]);
                $userId = (int)$pdo->lastInsertId();

                // Generate activation email
                $mail = new PHPMailer(true);
                $token = bin2hex(random_bytes(24));
                $expires = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');
                $pdo->prepare('INSERT INTO one_time_tokens (token, user_id, expires_at, used) VALUES (?,?,?,0)')
                    ->execute([$token, $userId, $expires]);
                $link = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/login.php?ott=' . $token;

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'managementsystemdocument@gmail.com';
                $mail->Password = 'athh fkxr rvzu dhce';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom('noreply@dms.com', 'DMS');
                $mail->addAddress($email, $displayName);
                $mail->isHTML(true);
                $mail->Subject = 'Activate your account';
                $mail->Body = "Hello <b>$displayName</b>,<br><br>
                    Click the link below to activate your account:<br>
                    <a href='$link'>$link</a><br><br>
                    This link will expire in 24 hours.";
                $mail->send();

                $pdo->prepare('INSERT INTO audit_logs (admin_id, action, target_type, target_id, details)
                               VALUES (?,?,?,?,?)')
                    ->execute([$_SESSION['user_id'], 'create_user', 'user', $userId, json_encode(['email' => $email])]);

                $success_message = 'User created successfully and activation email sent!';
            } else {
                $error_message = 'Please fill out all required fields.';
            }
        }
    } catch (Exception $e) {
        $error_message = 'An error occurred. Please try again.';
    }
}

$types = $pdo->query('SELECT id, name FROM user_types ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add New User - DMS Admin</title>

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

<!-- Root Styles -->
<link rel="stylesheet" href="../assets/css/root_colors_fonts.css">

<style>
/* ===== Global ===== */
body {
    font-family: var(--font-body);
    background-color: var(--light-gray);
    margin: 0;
    padding: 0;
}

/* Navbar */
.navbar-custom {
    background-color: var(--deep-navy);
    box-shadow: 0 4px 6px var(--shadow-light);
    padding: 0.5rem 1rem;
}
.navbar-brand {
    font-family: var(--font-headings);
    font-weight: 700;
    color: var(--white) !important;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.navbar-brand i {
    font-size: 1.3rem;
}
.navbar-custom .btn {
    color: var(--white);
    border: none;
}
.navbar-custom .btn:hover {
    background-color: var(--trust-blue);
}
.avatar-circle {
    width: 32px;
    height: 32px;
    background-color: var(--trust-blue);
    border-radius: 50%;
    color: var(--white);
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    justify-content: center;
    align-items: center;
    user-select: none;
}

/* User Dropdown Button */
.btn-primary.dropdown-toggle {
    background-color: var(--deep-navy);
    border-radius: 6px;
    height: 40px;
    border: none;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

/* Dropdown menu */
.dropdown-menu {
    border-radius: 12px;
    box-shadow: 0 4px 6px var(--shadow-light);
    min-width: 220px;
    padding: 0.5rem 0.5rem;
}

.dropdown-item {
    font-weight: 500;
    font-size: 0.9rem;
    color: var(--dark-gray);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 8px;
    padding: 0.5rem 1.5rem;
    transition: background-color 0.15s ease-in-out;
}

.dropdown-item:hover, .dropdown-item:focus {
    background-color: var(--sky-blue);
    color: var(--dark-gray);
}

.dropdown-item.text-danger {
    color: var(--red) !important;
}

/* ===== Header Section ===== */
.page-header-card {
    background: var(--trust-blue);
    padding: 1rem 1.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px var(--shadow-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}
.page-header-card .header-icon {
    font-size: 2rem;
    color: white;
    margin-right: 15px;
}

.page-header-card h2 {
    font-family: var(--font-headings);
    font-weight: 600;
    color: white;
}
.page-header-card p {
    margin: 0;
    color: white;
    opacity: 0.85;
    margin-top: 10px;
}

/* UI Improvement: Enhanced card styling with better spacing */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    transition: box-shadow 0.3s ease;
}
.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.card-header {
    background: var(--white) !important;
    border-radius: 12px 12px 0 0 !important;
    border-bottom: 2px solid var(--trust-blue);
    font-weight: 600;
    color: var(--dark-gray) !important;
    font-size: 1.1rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.card-body {
    padding: 1.5rem;
}
.card-body label {
    font-weight: 600;
    color: var(--dark-gray);
    margin-bottom: 0.5rem;
}
.card-header i {
    color: var(--trust-blue);
    font-size: 1.25rem;
}
.btn {
    font-weight: 500;
    border-radius: 8px;
    background: var(--trust-blue);
}
.btn:hover {
    background: var(--sky-blue);
    border: var(--sky-blue);
}

/* UI Improvement: Enhanced alert styling */
.alert {
    border-radius: 8px;
    padding: 1rem 1.25rem;
    font-weight: 500;
    margin-bottom: 1.5rem;
    border: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--green-teal);
    border-left: 4px solid var(--green-teal);
}
.alert-danger {
    background-color: rgba(220, 38, 38, 0.1);
    color: var(--red);
    border-left: 4px solid var(--red);
}

/* UI Improvement: Enhanced form styling with better spacing */
.form-label {
    font-weight: 600;
    color: var(--dark-gray);
    margin-bottom: 0.5rem;
}
.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid var(--light-gray-2);
    padding: 0.625rem 1rem;
    transition: all 0.2s ease;
}
.form-control:focus, .form-select:focus {
    border-color: var(--trust-blue);
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
    outline: none;
}
.form-select option:hover {
    background: var(--sky-blue);
}
.btn-back {
    background-color: var(--trust-blue);
    border: none;
    border-radius: 6px;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    transition: background-color 0.3s ease-in-out;
    color: var(--white);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-back:hover {
    background-color: var(--sky-blue);
    color: var(--white);
}
</style>
</head>

<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container px-4">
        <a class="navbar-brand" href="admin_home.php"><img src="../assets/images/hospitallogo.png" style="width:50px; height: 50px"> Blue Pulse</a>
        <!-- User Dropdown -->
        <div class="dropdown ms-auto">
            <button class="btn dropdown-toggle btn-primary" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-controls="userDropdownMenu">
                <div class="avatar-circle"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
                <span><?php echo ($user_role === 'admin') ? 'System Administrator' : htmlspecialchars($user_name); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" id="userDropdownMenu">
                <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle text-primary"></i> Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-cog text-secondary"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- UI Improvement: Better container spacing -->
<!-- Main Content -->
<div class="container mt-4 mb-5 px-4">
    <!-- Page Header -->
    <div class="page-header-card">
        <div>
            <h2><i class="fas fa-user-plus header-icon"></i>User Management</h2>
            <p>Add new users and define user types in the system.</p>
        </div>
        <a href="admin_home.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if($success_message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?></div>
    <?php elseif($error_message): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- UI Improvement: Better form layout with improved spacing -->
    <!-- Create User Type -->
    <div class="card">
        <div class="card-header"><i class="fas fa-layer-group me-2"></i>Create User Type</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="create_user_type">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Type Name</label>
                        <input type="text" class="form-control" name="type_name" placeholder="e.g., Doctor, Nurse" required>
                        <div class="form-text">Enter a name for the new user type</div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus-circle me-2"></i>Add Type
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Create User -->
    <div class="card">
        <div class="card-header"><i class="fas fa-user-plus me-2"></i>Create User Account</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="create_user">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" placeholder="example@gmail.com" required>
                        <div class="form-text">An activation email will be sent to this address</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">User Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="user_type_id" required>
                            <option value="">Select user type</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Choose the role for this user</div>
                    </div>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Create User & Send Activation Email
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
