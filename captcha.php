<?php
session_start();

$captcha_text = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
$_SESSION['captcha'] = $captcha_text;

header("Content-type: image/png");
$image = imagecreatetruecolor(120, 40);

$bg_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);
$line_color = imagecolorallocate($image, 100, 120, 180);
$pixel_color = imagecolorallocate($image, 0, 0, 255);

imagefilledrectangle($image, 0, 0, 120, 40, $bg_color);

// Add noise
for ($i = 0; $i < 50; $i++) {
    imagesetpixel($image, rand(0, 120), rand(0, 40), $pixel_color);
}
for ($i = 0; $i < 3; $i++) {
    imageline($image, rand(0, 120), rand(0, 40), rand(0, 120), rand(0, 40), $line_color);
}

// Add text
imagestring($image, 5, 30, 10, $captcha_text, $text_color);
imagepng($image);
imagedestroy($image);
?>