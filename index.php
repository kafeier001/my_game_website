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
<head>
    <title>游戏网站</title>
    <!-- 添加必要的CSS和JS文件 -->
    <style>
        /* 添加CSS样式 */
        #game-interface {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            width: 80%;
            margin: 0 auto;
        }
        #game-interface h2 {
            margin-bottom: 20px;
        }
        #game-interface img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        #game-interface button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #game-interface button:hover {
            background-color: #0056b3;
        }
        #game-canvas {
            width: 100%;
            height: 500px;
            background-color: #000;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            #game-canvas {
                height: 300px;
            }
        }
    </style>
</head>
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

    <!-- 游戏库模块 -->
    <div id="game-library">
        <h2>游戏库</h2>
        <form method="get" action="">
            <input type="text" id="search-bar" name="search" placeholder="搜索游戏...">
            <button type="submit">搜索</button>
        </form>
        <div id="filter-options">
            <select id="category-filter">
                <option value="all">所有分类</option>
                <option value="action">动作</option>
                <option value="adventure">冒险</option>
                <!-- 其他分类选项 -->
            </select>
        </div>
        <div id="games">
            <!-- 这里将动态加载游戏列表 -->
        </div>
    </div>

    <!-- 游戏界面模块 -->
    <div id="game-interface">
        <h2 id="game-title">游戏标题</h2>
        <div id="game-canvas"></div>
        <button id="start-game-button">开始游戏</button>
        <button id="pause-game-button">暂停游戏</button>
        <button id="restart-game-button">重新开始</button>
        <button id="fullscreen-game-button">全屏</button>
        <p id="game-score">得分: 0</p>
        <p id="game-timer">时间: 0</p>
    </div>

    <script>
    // 示例代码：加载游戏详细信息并显示
    function showGameDetails(gameId) {
        fetch(`/get_game_details.php?id=${gameId}`)
            .then(response => response.json())
            .then(game => {
                document.getElementById('game-title').innerText = game.title;
                document.getElementById('game-image').src = game.image_url;
                document.getElementById('game-description').innerText = game.description;
                document.getElementById('start-game-button').onclick = function() {
                    window.location.href = game.game_url;
                };
            });
    }

    // 游戏控制功能
    let gameInterval;
    let score = 0;
    let timer = 0;

    document.getElementById('start-game-button').onclick = startGame;
    document.getElementById('pause-game-button').onclick = pauseGame;
    document.getElementById('restart-game-button').onclick = restartGame;
    document.getElementById('fullscreen-game-button').onclick = toggleFullscreen;

    function startGame() {
        gameInterval = setInterval(() => {
            timer++;
            score++;
            document.getElementById('game-timer').innerText = '时间: ' + timer;
            document.getElementById('game-score').innerText = '得分: ' + score;
        }, 1000);
    }

    function pauseGame() {
        clearInterval(gameInterval);
    }

    function restartGame() {
        clearInterval(gameInterval);
        score = 0;
        timer = 0;
        document.getElementById('game-timer').innerText = '时间: ' + timer;
        document.getElementById('game-score').innerText = '得分: ' + score;
        startGame();
    }

    function toggleFullscreen() {
        const gameCanvas = document.getElementById('game-canvas');
        if (gameCanvas.requestFullscreen) {
            gameCanvas.requestFullscreen();
        } else if (gameCanvas.mozRequestFullScreen) { // Firefox
            gameCanvas.mozRequestFullScreen();
        } else if (gameCanvas.webkitRequestFullscreen) { // Chrome, Safari and Opera
            gameCanvas.webkitRequestFullscreen();
        } else if (gameCanvas.msRequestFullscreen) { // IE/Edge
            gameCanvas.msRequestFullscreen();
        }
    }
    </script>
    <p><?php echo $message; ?></p>
</body>
</html>