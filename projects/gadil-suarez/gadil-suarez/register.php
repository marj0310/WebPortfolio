<?php
session_start();

$host = 'localhost';
$db   = 'badil';
$user = 'root';
$pass = '';

// Connect to MySQL database
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!$password || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (empty($errors)) {
        // Check if email exists with prepared stmt
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'This email is already registered. Please login.';
        }
        mysqli_stmt_close($stmt);

        if (empty($errors)) {
            // Insert new user with prepared stmt
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password_hash);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header('Location: post.php');
                exit;
            } else {
                $errors[] = 'Database error: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register - Gadil</title>
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
  input[type="text"], input[type="email"], input[type="password"] {
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
  .login-link {
    margin-top: 15px;
    text-align: center;
    color: #ddd;
    font-size: 0.9rem;
  }
  .login-link a {
    color: #fff;
    font-weight: 700;
    text-decoration: none;
  }
  .login-link a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>
  <div class="container">
    <h2>Register</h2>
    <?php if (!empty($errors)): ?>
      <div class="alert">
        <?php foreach($errors as $error) echo htmlspecialchars($error) . '<br>'; ?>
      </div>
    <?php endif; ?>
    <form action="register.php" method="post" novalidate>
      <label for="username">Username</label>
      <input type="text" name="username" id="username" required minlength="3" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />

      <label for="email">Email</label>
      <input type="email" name="email" id="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />

      <label for="password">Password</label>
      <input type="password" name="password" id="password" required minlength="6" />

      <button type="submit">Register</button>
    </form>
    <div class="login-link">
      Already have an account? <a href="login.php">Login here</a>
    </div>
  </div>
</body>
</html>