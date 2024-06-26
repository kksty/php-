<?php
require_once('./function.php');


session_start();
$a = !empty($_GET['a']) ? $_GET['a'] : 'index';
$b = !empty($_GET['b']) ? $_GET['b'] : 'index';
$c = !empty($_GET['c']) ? $_GET['c'] : 'index';
$t = !empty($_GET['t']) ? $_GET['t'] : 'index';
$f = $_SERVER['REQUEST_METHOD'];


switch ($a) {
        # 网站首页
    case 'index':
        switch ($b) {
                # 首页  
            case 'index':
                require_once('./html/index.html');
                break;
                # 关于
            case 'about':
                require_once('./html/about.html');
                break;
                # 服务
            case 'service':
                $sql = "SELECT * FROM Company_Team";
                $result = Query($sql);
                // var_dump($result);
                // die;
                $Service_Team = '';
                foreach ($result as $k => $v) {
                    $Service_Team .= ' <div class="testimonial-item p-4 my-5">
                    <i class="fa fa-quote-right fa-3x text-light position-absolute top-0 end-0 mt-n3 me-4"></i>
                    <div class="d-flex align-items-end mb-4">
                        <img class="img-fluid flex-shrink-0" src="/html/img/testimonial-2.jpg" style="width: 80px; height: 80px;">
                        <div class="ms-4">
                            <h5 class="mb-1">' . $v['name'] .  '</h5>
                            <p class="m-0"> ' . $v['Profession'] .  '</p>
                        </div>
                    </div>
                    <p class="mb-0">' . $v['Remarks'] . '</p>
                    </div>';
                }
                require_once('./html/service.html');
                break;
            case 'contact':
                require_once('./html/contact.html');
                break;
            case 'login':
                require_once('./html/login.html');
                break;
            case 'loginSuccess':
                break;
            default:
                # code...
                break;
        }
        break;
}
