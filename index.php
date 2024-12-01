<?php
session_start();
include 'src/config.php';

// 处理登出请求
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        // 处理注册请求
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // 检查用户名或邮箱是否已存在
        $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $result = $conn->query($check_sql);

        if ($result->num_rows > 0) {
            $message = "用户名或邮箱已存在";
        } else {
            $sql = "INSERT INTO users (username, email, password_hash) VALUES ('$username', '$email', '$password')";
        
            if ($conn->query($sql) === TRUE) {
                $_SESSION['username'] = $username;
                $message = "注册成功";
            } else {
                $message = "错误: " . $conn->error;
            }
        }
    } elseif (isset($_POST['login'])) {
        // 处理登录请求
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['username'] = $username;
                $message = "登录成功";
            } else {
                $message = "密码错误";
            }
        } else {
            $message = "用户不存在";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<body>
    <?php if (isset($_SESSION['username'])): ?>
        <h2>欢迎, <?php echo $_SESSION['username']; ?></h2>
        <a href="index.php?logout=true">登出</a>
    <?php else: ?>
        <h2>用户注册</h2>
        <form method="post" action="">
            用户名: <input type="text" name="username"><br>
            邮箱: <input type="email" name="email"><br>
            密码: <input type="password" name="password"><br>
            <input type="submit" name="register" value="注册">
        </form>
        <h2>用户登录</h2>
        <form method="post" action="">
            用户名: <input type="text" name="username"><br>
            密码: <input type="password" name="password"><br>
            <input type="submit" name="login" value="登录">
        </form>
    <?php endif; ?>
    <p><?php echo $message; ?></p>
</body>
</html>