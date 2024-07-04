<?php
require_once('./function.php');
session_start(); // 启动会话

// 启动输出缓冲区以防止非 JSON 输出干扰
// ob_start();

// 获取表单提交的数据
$username = isset($_POST['username']) ? $_POST['username'] : NULL;
$password = isset($_POST['password']) ? $_POST['password'] : NULL;
$verificationCode = isset($_POST['verificationCode']) ? $_POST['verificationCode'] : NULL;

$sql = "SELECT * FROM user WHERE user = '$username' AND passwd = $password";
$result = Query($sql);

// 验证用户名和密码 
$isLoginValid = !empty($result);
// 从 session 中获取验证码
$storedVerificationCode = isset($_SESSION['code']) ? $_SESSION['code'] : NULL;

// 初始化响应数组
$response = [
    'loginSuccess' => false,
    'captchaValid' => false,
    'loginBlocked' => false,
    'message' => ''
];
// 用户若存在，获取锁定状态
$check_result = 10;
$sql = "SELECT * FROM user WHERE user = '$username'";
$result = Query($sql);
if (!empty($result)) {
    $check_sql = "SELECT `lock` FROM user WHERE user = '$username'";
    $check_result = Query($check_sql);
    $check_result = $check_result[0]['lock'];
}

// 若IP存在数据库，获取锁定状态
$ip = $_SERVER['REMOTE_ADDR'];
$ip_sql = "SELECT * FROM IP WHERE IP = '$ip'";
$ip_result = Query($ip_sql);
if (!empty($ip_result)) {
    $check_sql = "SELECT `lock` FROM IP WHERE IP = '$ip'";
    $check_ip = Query($check_sql);
    $check_ip = $check_ip[0]['lock'];
}

// 检查账户是否被锁定
if (isset($_SESSION['loginBlocked']) && $_SESSION['loginBlocked']) {
    $response['loginBlocked'] = true;
    $response['message'] = '验证码错误次数过多，账户已被锁定';
    // 验证码锁定解锁
    if (time() - $_SESSION['last_attempt_time'] > 180) {
        $_SESSION['nums'] = 0;
        $_SESSION['loginBlocked'] = false;
        $_SESSION['last_attempt_time'] = time(); // 更新成功登录的时间        
    }
} else if ($check_result == 0) {
    // 账号密码锁定解锁
    $response['message'] = '用户名密码错误次数过多，账户已被锁定';
    // 从数据库获取上次登陆失败时间
    $check_sql = "SELECT lastFailureTime FROM user WHERE user = '$username'";
    $check_result = Query($check_sql);
    $lastFailureTime = $check_result[0]['lastFailureTime'];
    if ((time() - $lastFailureTime > 60)) {
        $lock_sql = "UPDATE user SET `lock` = 1 WHERE user = '$username'";
        $update_sql = "UPDATE user SET NumberFailures = 0 WHERE user = '$username'";
        Query($lock_sql);
        Query($update_sql);
    }
} else if ($check_ip == 0) {
    $response['message'] = '当前IP短时间内多次登陆失败，IP已被锁定';
    // 从数据库获取上次登陆失败时间
    $check_sql = "SELECT lastFailureTime FROM IP WHERE IP = '$ip'";
    $check_ip =  Query($check_sql);
    $lastFailureTime = $check_ip[0]['lastFailureTime'];
    if ((time() - $lastFailureTime > 60)) {
        $lock_sql = "UPDATE IP SET `lock` = 1 WHERE IP = '$ip'";
        $update_sql = "UPDATE IP SET attempts = 0 WHERE IP = '$ip'";
        Query($lock_sql);
        Query($update_sql);
    }
} else {
    // 验证验证码前先检查验证user码是否为空
    if (empty($verificationCode)) {
        $response['message'] = '验证码不能为空...';
    } else {
        // 验证验证码 
        $isCaptchaValid = (strcasecmp($verificationCode, $storedVerificationCode) == 0);
        $response['captchaValid'] = $isCaptchaValid;

        if ($isCaptchaValid) {
            if ($isLoginValid) {
                $response['loginSuccess'] = true;
                $response['message'] = '登录成功！';
                $_SESSION['nums'] = 0;
                $_SESSION['last_attempt_time'] = time(); // 更新成功登录的时间
                $_SESSION['code'] = NULL;
                // 重置数据库中的 NumberFailures
                $update_sql = "UPDATE user SET NumberFailures = 0 WHERE user = '$username'";
                $lock_sql = "UPDATE user SET `lock` = 1 WHERE user = '$username'";
                Query($update_sql);
                Query($lock_sql);
            } else {
                $response['message'] = '登录失败，用户名或密码错误！';
                // 检查用户的 NumberFailures,lastFailureTime
                $check_sql = "SELECT NumberFailures, lastFailureTime FROM user WHERE user = '$username'";
                $check_result = Query($check_sql);
                // 从数据库获取登陆失败次数和上次登陆失败时间
                $numberFailures = $check_result[0]['NumberFailures'];
                $lastFailureTime = $check_result[0]['lastFailureTime'];
                // 失败次数到达五次禁止登陆
                if ($numberFailures >= 5 && (time() - $lastFailureTime <= 60)) { // 60秒
                    $response['message'] = '短时间内错误次数达到五次，禁止登陆...';
                    $lock_sql = "UPDATE user SET `lock` = 0 WHERE user = '$username'";
                    Query($lock_sql);
                    $time = time();
                    $update_sql = "UPDATE user SET lastFailureTime = '$time' WHERE user = '$username'";
                    Query($update_sql);
                } else {
                    // 只要两次输入间隔大于一定时间，就重新初始化number
                    if ((time() - $lastFailureTime > 60)) {
                        $time = time();
                        $update_sql = "UPDATE user SET NumberFailures = 0, lastFailureTime = '$time' WHERE user = '$username'";
                        Query($update_sql);
                    } else {
                        $time = time();
                        $update_sql = "UPDATE user SET NumberFailures = NumberFailures + 1, lastFailureTime = '$time' WHERE user = '$username'";
                        Query($update_sql);
                    }
                }
                // 真实IP查询
                $ip = $_SERVER['REMOTE_ADDR'];
                // 查询数据库有无对应ip，若没有则添加，若有则判断ip尝试次数
                $ip_sql = "SELECT * FROM IP WHERE IP = '$ip'";
                $ip_result = Query($ip_sql);

                if (empty($ip_result)) {
                    $time = time();
                    $sql = "INSERT INTO IP (IP, attempts, lastFailureTime) VALUES ('$ip', 1, '$time')";
                    Query($sql);
                } else {
                    $check_ip = "SELECT attempts, lastFailureTime FROM IP WHERE IP = '$ip'";
                    $check_result = Query($check_ip);
                    $attempts = $check_result[0]['attempts'];
                    $lastFailureTime = $check_result[0]['lastFailureTime'];
                    // 锁定IP
                    if ($attempts >= 5 && (time() - $lastFailureTime <= 60)) { // 60秒
                        $response['message'] = '当前IP短时间内多次登陆失败，禁止登陆...';
                        $lock_sql = "UPDATE IP SET `lock` = 0 WHERE IP = '$ip'";
                        Query($lock_sql);
                        $time = time();
                        $update_sql = "UPDATE IP SET lastFailureTime = '$time' WHERE IP = '$ip'";
                        Query($update_sql);
                    } else {
                        if (time() - $lastFailureTime > 60) {
                            $time = time();
                            $update_sql = "UPDATE IP SET attempts = 0, lastFailureTime = '$time' WHERE IP = '$ip'";
                            Query($update_sql);
                        } else {
                            $time = time();
                            $update_sql = "UPDATE IP SET attempts = attempts + 1, lastFailureTime = '$time' WHERE IP = '$ip'";
                            Query($update_sql);
                        }
                    }
                }
            }
        } else {
            $response['message'] = '验证码错误!';
            if (!isset($_SESSION['nums'])) {
                $_SESSION['nums'] = 0;
            }

            // 检查当前时间与上次尝试时间的间隔是否超过设定的时间阈值（例如 1 小时）
            if (time() - $_SESSION['last_attempt_time'] > 180) {
                $_SESSION['nums'] = 0;
                $_SESSION['last_attempt_time'] = time();
            }
            $_SESSION['nums'] += 1;

            // 检查尝试次数是否达到5次
            if ($_SESSION['nums'] >= 5) {
                $_SESSION['loginBlocked'] = true;
                $response['loginBlocked'] = true;
                $response['message'] = '验证码输入次数已达五次，账户已被临时锁定。';
                session_write_close(); // 关闭 session 以防止并发问题
            }
        }
    }
}

// 清除缓冲区中的所有内容
// ob_end_clean();

// 设置响应头为 JSON 格式
header('Content-Type: application/json');

// 输出 JSON 响应
echo json_encode($response);
