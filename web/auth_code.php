<?php
session_start();
$image = imagecreatetruecolor(80,42);
$bgcolor = imagecolorallocate($image,255,255,255);
imagefill($image,0,0,$bgcolor);

$auto_code = '';
for($i=0;$i<4;$i++){
    $fontsize = 15;
    $fontcolor = imagecolorallocate($image,rand(0,120),rand(0,120),rand(0,120));
    $data = 'abcdefghijkmnpqrstuvwxyABCDEFGHJKLMNPQRSTUVWXY23456789';
    $fontcontent = substr($data,rand(0,strlen($data)),1);
    $auto_code .= $fontcontent;
    $x = ($i*100/5)+rand(5,10);  //验证码所在宽度位置
    $y = rand(5,10); //验证码所在高度位置
    imagestring($image,$fontsize,$x,$y,$fontcontent,$fontcolor);
}
$_SESSION['authcode'] = $auto_code;

//增加点干扰元素
for($i=0;$i<200;$i++){
    $pointcolor = imagecolorallocate($image,rand(50,200),rand(50,200),rand(50,200));
    imagesetpixel($image,rand(1,79),rand(1,41),$pointcolor);
}

//增加线干扰元素
for($i=0;$i<5;$i++){
    $linecolor = imagecolorallocate($image,rand(80,220),rand(80,220),rand(80,220));
    imageline($image,rand(1,79),rand(1,41),rand(1,79),rand(1,41),$linecolor);
}

header("content-type:image/png");
imagepng($image);

imagedestroy($image);