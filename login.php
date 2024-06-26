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

// 检查账户是否被锁定
if (isset($_SESSION['loginBlocked']) && $_SESSION['loginBlocked']) {
    $response['loginBlocked'] = true;
    $response['message'] = '账户已被锁定';
    if (time() - $_SESSION['last_attempt_time'] > 180) {
        $_SESSION['nums'] = 0;
        $_SESSION['loginBlocked'] = false;
        $_SESSION['last_attempt_time'] = time(); // 更新成功登录的时间
    }
} else {
    // 验证验证码前先检查验证码是否为空
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
                Query($update_sql);
            } else {
                $response['message'] = '登录失败，用户名或密码错误！';
                $update_sql = "UPDATE user SET NumberFailures = NumberFailures + 1, lastFailureTime = NOW() WHERE user = '$username'";
                Query($update_sql);
                // 检查用户的 NumberFailures
                $check_sql = "SELECT NumberFailures, lastFailureTime FROM user WHERE user = '$username'";
                $check_result = Query($check_sql);
                // 从数据库获取登陆失败次数和上次登陆时间
                $numberFailures = $check_result[0]['NumberFailures'];
                $lastFailureTime = strtotime($check_result[0]['lastFailureTime']);
                // 失败次数到达五次禁止登陆
                if ($numberFailures >= 5 && (time() - $lastFailureTime <= 60)) { // 60秒
                    $response['message'] = '短时间内错误次数达到五次，禁止登陆...';
                    $_SESSION['loginBlocked'] = true;
                    $response['loginBlocked'] = true;
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
