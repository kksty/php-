	<?php
    // session_start();   //启动绘画
    // if (isset($_POST["submit"])) {

    //     $user = $_SESSION['code']; //服务器临时保存yzm
    //     $str = $_POST["a"];
    //     var_dump($_SESSION);
    //     if (strcasecmp($str, $user) == 0)   //比较  等于0 两个值就为相等
    //         echo "<script>alert('验证码正确!!!');</script>";
    //     // echo '验证码正确';
    //     else
    //         // echo '验证码有误';
    //         echo "<script>alert('你的验证有误，请重新输入!');</script>";
    // }
    die("验证码错误次数已达到五次，禁止登陆!");
    ?>
