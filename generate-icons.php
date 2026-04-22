<?php
/**
 * PWA Icon Generator using PHP GD
 * 
 * This script generates placeholder PWA icons.
 * You should replace these with your actual app icons.
 * 
 * Usage: php generate-icons.php
 */

$imageDir = __DIR__ . '/src/public/images';
if (!is_dir($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// Icon sizes to generate
$sizes = [192, 256, 384, 512];

// Gradient colors (Blue theme matching manifest.json)
$colorStart = ['r' => 59, 'g' => 130, 'b' => 246];  // #3b82f6
$colorEnd = ['r' => 37, 'g' => 99, 'b' => 235];     // #2563eb

foreach ($sizes as $size) {
    // Create image
    $image = imagecreatetruecolor($size, $size);
    
    // Enable alpha blending
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    // Draw gradient background
    for ($y = 0; $y < $size; $y++) {
        $ratio = $y / $size;
        $r = intval($colorStart['r'] + ($colorEnd['r'] - $colorStart['r']) * $ratio);
        $g = intval($colorStart['g'] + ($colorEnd['g'] - $colorStart['g']) * $ratio);
        $b = intval($colorStart['b'] + ($colorEnd['b'] - $colorStart['b']) * $ratio);
        
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $y, $size, $y, $color);
    }
    
    // Draw rounded corners
    $radius = intval($size / 8);
    $w = imagecolorallocate($image, 255, 255, 255);
    
    // Create a circle mask for corners
    $c = imagecreatetruecolor($size, $size);
    imagesavealpha($c, true);
    $transparent = imagecolorallocatealpha($c, 0, 0, 0, 127);
    imagefill($c, 0, 0, $transparent);
    
    $white = imagecolorallocate($c, 255, 255, 255);
    imagefilledrectangle($c, 0, 0, $size, $size, $white);
    
    // Draw white checkmark in center
    $fontSize = intval($size / 2.5);
    $centerX = intval($size / 2);
    $centerY = intval($size / 2);
    
    // Create a small image for text rendering
    $textImg = imagecreatetruecolor($size, $size);
    imagesavealpha($textImg, true);
    $transparent = imagecolorallocatealpha($textImg, 0, 0, 0, 127);
    imagefill($textImg, 0, 0, $transparent);
    
    $white = imagecolorallocate($textImg, 255, 255, 255);
    
    // Draw checkmark using simple lines
    $checkSize = intval($size / 3);
    $x1 = $centerX - intval($checkSize / 2);
    $y1 = $centerY;
    $x2 = $centerX - intval($checkSize / 6);
    $y2 = $centerY + intval($checkSize / 4);
    $x3 = $centerX + intval($checkSize / 2);
    $y3 = $centerY - intval($checkSize / 4);
    
    $lineWidth = intval($size / 32);
    for ($i = 0; $i < $lineWidth; $i++) {
        imagelinethick($image, $x1, $y1 + $i, $x2, $y2 + $i, $w, 2);
        imagelinethick($image, $x2, $y2 + $i, $x3, $y3 + $i, $w, 2);
    }
    
    // Save the PNG
    $filename = $imageDir . '/icon-' . $size . 'x' . $size . '.png';
    imagepng($image, $filename, 9);
    imagedestroy($image);
    
    echo "✓ Generated: icon-{$size}x{$size}.png\n";
}

echo "\n✅ All icons generated successfully!\n";
echo "Location: {$imageDir}\n\n";

/**
 * Helper function to draw thick lines
 */
function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thickness = 1)
{
    if ($thickness == 1) {
        return imageline($image, $x1, $y1, $x2, $y2, $color);
    }
    
    $t = $thickness / 2 - 0.5;
    if ($x1 == $x2 && $y1 == $y2) {
        return imagefilledellipse($image, $x1, $y1, $thickness, $thickness, $color);
    }
    
    $k = ($y2 - $y1) / ($x2 - $x1);
    $a = $thickness / sqrt(1 + pow($k, 2));
    $points = array(
        round(($x1 - $a * (1 + $k) / sqrt(1 + pow($k, 2)))),
        round(($y1 + $a * (1 - $k) / sqrt(1 + pow($k, 2)))),
        round(($x1 + $a * (1 + $k) / sqrt(1 + pow($k, 2)))),
        round(($y1 - $a * (1 - $k) / sqrt(1 + pow($k, 2)))),
        round(($x2 + $a * (1 + $k) / sqrt(1 + pow($k, 2)))),
        round(($y2 - $a * (1 - $k) / sqrt(1 + pow($k, 2)))),
        round(($x2 - $a * (1 + $k) / sqrt(1 + pow($k, 2)))),
        round(($y2 + $a * (1 - $k) / sqrt(1 + pow($k, 2)))),
    );
    
    imagefilledpolygon($image, $points, 4, $color);
    return imageellipse($image, $x1, $y1, $thickness, $thickness, $color);
}
?>
