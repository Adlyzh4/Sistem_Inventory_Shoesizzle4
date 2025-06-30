<?php
// auth/login.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';


if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $errors[] = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $user['password'] === $password) {
            $_SESSION['user_id'] = $user['user_id'];
            logAktivitas($pdo, $_SESSION['user_id'], "Login ke sistem");
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            header('Location: ../pages/dashboard/index.php');
            exit;
        } else {
            $errors[] = 'Username atau password salah.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin - ShoeSizzle</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card shadow-sm">
          <div class="card-header text-center fw-bold">Login Admin</div>
          <div class="card-body">
            <?php if ($errors): ?>
              <div class="alert alert-danger">
                <?php foreach ($errors as $error): echo "$error<br>"; endforeach; ?>
              </div>
            <?php endif; ?>
            <form method="post">
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
