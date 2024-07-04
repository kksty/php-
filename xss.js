function ajaxRequest(method, url, callback) {
    // 创建 XMLHttpRequest 对象
    var xhr = new XMLHttpRequest();

    // 准备发送请求
    xhr.open(method, url, true);

    // 设置请求头（根据需要）
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // 处理响应
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            callback(xhr.responseText);
        }
    };

    // 发送请求，将当前页面 URL 作为数据
    if (method === 'GET') {
        xhr.send();
    } else {
        var currentUrl = window.location.href;
        xhr.send(`url=${encodeURIComponent(currentUrl)}`);
    }
}

// 使用示例
ajaxRequest('POST', './xss.php', function(response) {
    console.log(response);
});
