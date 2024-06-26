<?php

function Query($sql)
{
    # 链接数据库
    $db = @new mysqli('127.0.0.1', 'zhangsan', 'zhangsan666', 'company', 3306);

    if (mysqli_connect_errno() != 0) {
        echo 'MYSQL 连接错误！';
        echo mysqli_connect_error();
        return false; // 返回 false 表示连接失败
    }

    // 设置字符集为 utf8mb4
    $db->set_charset("utf8mb4");

    # sql语句
    $result = $db->query($sql);

    // 非SELECT类型的查询，直接返回执行结果
    if (!strstr(strtolower($sql), 'select')) {
        // 执行成功返回 true，失败返回 false
        if ($result === true) {
            $db->close();
            return true;
        } else {
            $db->close();
            return false;
        }
    }

    # 处理SELECT查询结果
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // 关闭数据库连接
    $db->close();

    return $data;
}
