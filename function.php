<?php

function Query($sql)
{
    # 链接数据库
    $db = @new mysqli('127.0.0.1', 'zhangsan', 'zhangsan666', 'company', 3306);

    if (mysqli_connect_errno() != 0) {
        echo 'MYSQL 连接错误！';
        echo mysqli_connect_error();
    }
    // 设置字符集为 utf8mb4
    $db->set_charset("utf8mb4");
    # sql语句
    $result = $db->query($sql);

    # 关闭语句
    $db->close();
    if (strstr(strtolower($sql), 'select') != FALSE) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return $result;
    }
}
