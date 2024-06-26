<?php
session_start();
header('Content-type: image/gif');
$image_w = 100;
$image_h = 25;
$number = range(0, 9);
$character = range("Z", "A");
$result = array_merge($number, $character); /*array_merge函数是将两个数组按先
	后的顺序合并到一起，合并到一起组成一个新的数组*/
$string = "";
$len = count($result);
for ($i = 0; $i < 4; $i++) {
  $new_number[$i] = $result[rand(0, $len - 1)];
  $string = $string . $new_number[$i];
}
$_SESSION['code'] = $string;
// var_dump($_SESSION);
// die;
$check_image = imagecreatetruecolor($image_w, $image_h); /*创建一个画布，使用PHP的GD Library创建新的真彩色
	图像,函数返回图像资源标识符*/
$white = imagecolorallocate($check_image, 255, 255, 255); // 设置白色的图形
$black = imagecolorallocate($check_image, 0, 0, 0); // 设置黑色的图形  
imagefill($check_image, 0, 0, $white);/*imagefill()函数在 image 图像的坐标x,y (图像左上角为0, 0) 
	处用 color 颜色执行区域填充*/
for ($i = 0; $i < 100; $i++) {
  imagesetpixel($check_image, rand(0, $image_w), rand(0, $image_h), $black);/*设置干扰，使用 
		imagesetpixel()函数给图片添加干扰点。*/
}
for ($i = 0; $i < count($new_number); $i++) {
  $x = mt_rand(1, 8) + $image_w * $i / 4;  /*该函数是产生随机值的更好选择，返回结果的
		速度是 rand() 函数的 4 倍 */
  $y = mt_rand(1, $image_h / 4); //指定生成位置X、Y轴偏移量
  $color = imagecolorallocate($check_image, mt_rand(0, 200), mt_rand(0, 200), mt_rand(0, 200));
  imagestring($check_image, 5, $x, $y, $new_number[$i], $color);/*imagestring()函数是PHP中的内置函数，
		用于水平绘制字符串。此函数在给定位置绘制字符串*/
}
imagepng($check_image); //以PNG格式将图像输出到浏览器或文件
imagedestroy($check_image);
