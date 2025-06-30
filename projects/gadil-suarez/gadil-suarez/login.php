<?php
session_start();

$host = 'localhost';
$db   = 'badil';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!$password) {
        $errors[] = 'Please enter your password.';
    }

    if (empty($errors)) {
        // Prepare and execute select query
        $stmt = mysqli_prepare($conn, "SELECT id, username, password_hash FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $username, $password_hash);
        if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($password, $password_hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header('Location: post.php');
                exit;
            } else {
                $errors[] = 'Invalid email or password.';
            }
        } else {
            $errors[] = 'Invalid email or password.';
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login - Gadil</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg,rgb(47, 17, 154),rgb(182, 147, 217));
    margin: 0; padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    color: #fff;
  }
  .container {
    background: rgba(255,255,255,0.15);
    padding: 25px 30px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    width: 320px;
    max-width: 95vw;
  }
  h2 {
    margin-top: 0;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 700;
    text-shadow: 0 0 8px rgba(0,0,0,0.3);
  }
  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
  }
  input[type="email"], input[type="password"] {
    width: 93%;
    padding: 10px 12px;
    margin-bottom: 15px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    outline: none;
  }
  button {
    width: 100%;
    background: #5a4de1;
    border: none;
    padding: 12px 0;
    border-radius: 10px;
    color: white;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 6px 15px rgba(90,77,225,0.5);
    transition: background 0.3s ease;
  }
  button:hover {
    background: #7e6ef9;
  }
  .alert {
    background-color: #e56a6a;
    color: #5a0f0f;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    text-align: center;
  }
  .register-link {
    margin-top: 15px;
    text-align: center;
    color: #ddd;
    font-size: 0.9rem;
  }
  .register-link a {
    color: #fff;
    font-weight: 700;
    text-decoration: none;
  }
  .register-link a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>
  <div class="container">
    <h2>Login</h2>
    <?php if (!empty($errors)): ?>
      <div class="alert">
        <?php foreach($errors as $error) echo htmlspecialchars($error) . '<br>'; ?>
      </div>
    <?php endif; ?>
    <form action="login.php" method="post" novalidate>
      <label for="email">Email</label>
      <input type="email" name="email" id="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />

      <label for="password">Password</label>
      <input type="password" name="password" id="password" required />

      <button type="submit">Login</button>
    </form>
    <div class="register-link">
      Don't have an account? <a href="register.php">Register here</a>
    </div>
  </div>
</body>
</html>