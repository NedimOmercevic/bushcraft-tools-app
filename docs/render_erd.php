<?php
/**
 * Renders docs/erd.png for Milestone 1. Run: php docs/render_erd.php
 */
$w = 1400;
$h = 820;
$im = imagecreatetruecolor($w, $h);
$cream = imagecolorallocate($im, 244, 234, 213);
$bark = imagecolorallocate($im, 61, 43, 31);
$forest = imagecolorallocate($im, 45, 74, 62);
$ember = imagecolorallocate($im, 196, 92, 38);
$white = imagecolorallocate($im, 255, 250, 240);
$ink = imagecolorallocate($im, 31, 23, 18);
$line = imagecolorallocate($im, 90, 70, 52);
$font = 'C:/Windows/Fonts/arial.ttf';
$fontb = 'C:/Windows/Fonts/arialbd.ttf';
if (!is_readable($fontb)) {
    $fontb = $font;
}

imagefilledrectangle($im, 0, 0, $w, $h, $cream);
imagettftext($im, 22, 0, 40, 48, $bark, $fontb, 'Bushcraft Tools — Draft ERD (Milestone 1)');
imagettftext($im, 12, 0, 40, 74, $forest, $font, 'Planning only. Five entities: User, Category, Tool, Review, Favorite.');

function entityBox($im, $x, $y, $bw, $bh, $title, $rows, $header, $body, $ink, $font, $fontb) {
    imagefilledrectangle($im, $x, $y, $x + $bw, $y + $bh, $body);
    imagerectangle($im, $x, $y, $x + $bw, $y + $bh, $ink);
    imagefilledrectangle($im, $x, $y, $x + $bw, $y + 36, $header);
    imagerectangle($im, $x, $y, $x + $bw, $y + 36, $ink);
    imagettftext($im, 13, 0, $x + 12, $y + 24, imagecolorallocate($im, 255, 255, 255), $fontb, $title);
    $yy = $y + 58;
    foreach ($rows as $row) {
        imagettftext($im, 11, 0, $x + 12, $yy, $ink, $font, $row);
        $yy += 22;
    }
}

$users = [40, 120, 280, 250];
$cats = [40, 460, 280, 180];
$tools = [520, 250, 320, 250];
$reviews = [1020, 120, 320, 230];
$favs = [1020, 460, 320, 210];

entityBox($im, $users[0], $users[1], $users[2], $users[3], 'USER', [
    'PK  id',
    '    email  UNIQUE',
    '    password_hash',
    '    display_name',
    '    role  (user | admin)',
    '    created_at',
], $bark, $white, $ink, $font, $fontb);

entityBox($im, $cats[0], $cats[1], $cats[2], $cats[3], 'CATEGORY', [
    'PK  id',
    '    name',
    '    slug  UNIQUE',
], $forest, $white, $ink, $font, $fontb);

entityBox($im, $tools[0], $tools[1], $tools[2], $tools[3], 'TOOL', [
    'PK  id',
    'FK  category_id  → CATEGORY',
    '    name',
    '    brand',
    '    description',
    '    price, stock',
], $ember, $white, $ink, $font, $fontb);

entityBox($im, $reviews[0], $reviews[1], $reviews[2], $reviews[3], 'REVIEW', [
    'PK  id',
    'FK  user_id  → USER',
    'FK  tool_id  → TOOL',
    '    rating  (1–5)',
    '    comment',
    '    created_at',
], $bark, $white, $ink, $font, $fontb);

entityBox($im, $favs[0], $favs[1], $favs[2], $favs[3], 'FAVORITE', [
    'PK  id',
    'FK  user_id  → USER',
    'FK  tool_id  → TOOL',
    '    created_at',
    'UQ  (user_id, tool_id)',
], $forest, $white, $ink, $font, $fontb);

function rel($im, $x1, $y1, $x2, $y2, $label, $color, $font) {
    imageline($im, $x1, $y1, $x2, $y2, $color);
    imagefilledellipse($im, $x1, $y1, 8, 8, $color);
    imagefilledellipse($im, $x2, $y2, 8, 8, $color);
    $mx = (int)(($x1 + $x2) / 2);
    $my = (int)(($y1 + $y2) / 2) - 6;
    imagettftext($im, 10, 0, $mx - 20, $my, $color, $font, $label);
}

rel($im, 320, 550, 520, 400, '1 : N', $line, $font);
rel($im, 320, 220, 1020, 200, '1 : N', $line, $font);
rel($im, 840, 340, 1020, 230, '1 : N', $line, $font);
rel($im, 320, 280, 1020, 540, 'N : M', $line, $font);
rel($im, 840, 430, 1020, 560, 'via', $line, $font);

imagettftext($im, 11, 0, 40, 780, $bark, $font, 'Category 1:N Tool   |   User 1:N Review   |   Tool 1:N Review   |   User N:M Tool through Favorite');

$out = __DIR__ . '/erd.png';
imagepng($im, $out);
imagedestroy($im);
echo "Wrote $out\n";
