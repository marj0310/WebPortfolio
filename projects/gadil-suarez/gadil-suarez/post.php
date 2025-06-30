<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$host = 'localhost';
$db   = 'badil';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Array of forbidden words to censor (case insensitive)
$forbidden_words = ['bading', 'gago', 'gay']; // Customize your censored words here

$errors = [];
$success = '';
$censored_title = '';
$censored_content = '';

/**
 * Replace forbidden words with asterisks in a string, case-insensitive
 * @param string $text
 * @param array $words array of forbidden words
 * @return string
 */
function censor_text($text, $words) {
    foreach ($words as $word) {
        $word_escaped = preg_quote($word, '/');
        $replace = str_repeat('*', strlen($word));
        // Use regex to replace whole words case-insensitive
        $text = preg_replace_callback("/\b{$word_escaped}\b/i", function($matches) use ($replace) {
            return $replace;
        }, $text);
    }
    return $text;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['post_title'] ?? '');
    $content = trim($_POST['post_content'] ?? '');

    // Censor inputs only for display
    $censored_title = censor_text($title, $forbidden_words);
    $censored_content = censor_text($content, $forbidden_words);

    if (!$title) {
        $errors[] = 'Post title is required.';
    }
    if (!$content) {
        $errors[] = 'Post content is required.';
    }

    if (empty($errors)) {
        // Save original (uncensored) title and content to DB
        $stmt = mysqli_prepare($conn, "INSERT INTO posts (user_id, post_title, post_content) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $_SESSION['user_id'], $title, $content);

        if (mysqli_stmt_execute($stmt)) {
            $success = 'Post submitted successfully!';
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Post - Gadil</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg,rgb(47, 17, 154),rgb(182, 147, 217));
    margin: 0; padding: 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    height: 100vh;
    color: #fff;
    padding-top: 30px;
    align-items: center;
    justify-content: center;
    align-content: center;
    justify-items: center;
  }
  .container {
    background: rgba(255,255,255,0.15);
    padding: 25px 30px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    width: 350px;
    max-width: 95vw;
    
  }
  h2 {
    margin-top: 0;
    margin-bottom: 10px;
    text-align: center;
    font-weight: 700;
    text-shadow: 0 0 8px rgba(0,0,0,0.3);
  }
  .welcome {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
    font-size: 1rem;
    color: #cfcce0;
  }
  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
  }
  input[type="text"], textarea {
    width: 93%;
    padding: 10px 12px;
    margin-bottom: 15px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    outline: none;
    resize: vertical;
  }
  textarea {
    min-height: 80px;
  }
  button {
    width: 100%;
    background:rgb(117, 105, 243);
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
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    text-align: center;
  }
  .alert-error {
    background-color: #e56a6a;
    color: #5a0f0f;
  }
  .alert-success {
    background-color: #7dd57d;
    color: #1f3c17;
    white-space: pre-wrap;
    text-align: left;
  }
  .logout-link {
    margin-top: 15px;
    text-align: center;
  }
  .logout-link a {
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
  }
  .logout-link a:hover {
    text-decoration: none;
    color:rgb(32, 28, 54);
  }
</style>
<script>
function censorText(text, words) {
  words.forEach(word => {
    const pattern = new RegExp('\\b' + word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'gi');
    const stars = '*'.repeat(word.length);
    text = text.replace(pattern, stars);
  });
  return text;
}
window.addEventListener('DOMContentLoaded', () => {
  const forbiddenWords = ['bitch', 'gay', 'bading', 'foc u' ,'fuck', 'fuckshit','gago', 'tanga', 'butthole','fuck you','pakyu', 'faggot', 'baddai']; // Customize these
  const form = document.getElementById('postForm');
  const titleInput = document.getElementById('post_title');
  const contentInput = document.getElementById('post_content');

  form.addEventListener('submit', function(e) {
    if (!titleInput.value.trim() || !contentInput.value.trim()) {
      e.preventDefault();
      alert('Please fill out both the title and the content fields.');
      return;
    }
    titleInput.value = censorText(titleInput.value, forbiddenWords);
    contentInput.value = censorText(contentInput.value, forbiddenWords);
  });
});
</script>
</head>
<body>
  <div class="container">
    <h2>New Post</h2>
    <div class="welcome">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $error) echo htmlspecialchars($error) . "<br>"; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($success) ?><br><br>
        <strong>Censored Title:</strong><br>
        <?= htmlspecialchars($censored_title) ?><br><br>
        <strong>Censored Content:</strong><br>
        <?= nl2br(htmlspecialchars($censored_content)) ?>
      </div>
    <?php endif; ?>

    <form id="postForm" method="post" action="post.php" novalidate>
      <label for="post_title">Post Title</label>
      <input type="text" id="post_title" name="post_title" />

      <label for="post_content">Post Content</label>
      <textarea id="post_content" name="post_content"></textarea>

      <button type="submit">Submit Post</button>
    </form>

    <div class="logout-link">
      <a href="#" id="logout-link">Logout</a>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
        const LogoutLink = document.getElementById('logout-link');
        if(LogoutLink){
            LogoutLink.addEventListener('click', function (e){
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')){
                    window.location.href ='post.php?logout=1';
                }
            });
        }
    });
  </script>
</body>
</html>
</content>
</create_file>

