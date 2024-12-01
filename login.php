<?php
session_start();
include 'src/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['username'] = $username;
            echo "登录成功";
        } else {
            echo "密码错误";
        }
    } else {
        echo "用户不存在";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<body>
    <?php if (isset($_SESSION['username'])): ?>
        <h2>欢迎, <?php echo $_SESSION['username']; ?></h2>
        <a href="logout.php">登出</a>
    <?php else: ?>
        <h2>用户登录</h2>
        <form method="post" action="login.php">
            用户名: <input type="text" name="username"><br>
            密码: <input type="password" name="password"><br>
            <input type="submit" value="登录">
        </form>
    <?php endif; ?>
</body>
</html>