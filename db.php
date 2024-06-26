
<?php
session_start();

$string = "your_value";  // 给要存储的值进行赋值

$_SESSION['string'] = $string;  // 存储值到会话

echo "session: " . $_SESSION['string'];  // 输出会话值

?>
