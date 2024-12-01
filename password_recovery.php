<!DOCTYPE html>
<html>
<body>
    <h2>密码恢复</h2>
    <form method="post" action="password_recovery.php">
        邮箱: <input type="email" name="email"><br>
        <input type="submit" value="发送重置链接">
    </form>
    <?php
        include 'src/config.php';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST['email'];

            $sql = "SELECT * FROM users WHERE email='$email'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $token = bin2hex(random_bytes(50));
                $sql = "UPDATE users SET reset_token='$token' WHERE email='$email'";
                if ($conn->query($sql) === TRUE) {
                    // 发送密码重置链接到用户邮箱
                    // 这里假设你已经配置了邮件服务
                    $reset_link = "http://yourwebsite.com/reset_password.php?token=$token";
                    mail($email, "密码重置", "点击链接重置密码: $reset_link");

                    echo "密码重置链接已发送到您的邮箱";
                } else {
                    echo "错误: " . $conn->error;
                }
            } else {
                echo "邮箱不存在";
            }

            $conn->close();
        }
    ?>
</body>
</html>