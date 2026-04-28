<?php
/**
 * Generative Identity — Unique visual patterns from content
 * 
 * Generates deterministic images based on text content.
 * Same input always produces the same output.
 * 
 * No external dependencies. Just PHP + GD.
 * 
 * @author Shinobis (https://shinobis.com)
 * @license MIT
 */

// === CONFIGURATION ===
define('IMG_SIZE', 1200);
define('GRID_COLS', 32);
define('CUBE_SIZE', IMG_SIZE / GRID_COLS);
define('CENTER_CHAR', '忍'); // Set to '' to disable

/**
 * Generate a PNG image from title and content
 * 
 * @param int    $id        Unique identifier
 * @param string $title     Title text (drives pattern)
 * @param string $content   Content text (drives color + pattern)
 * @param string $lang      Language code (for filename)
 * @param string $outputDir Output directory (with trailing slash)
 * @return string Path to generated file
 */
function generateImage($id, $title, $content, $lang = 'en', $outputDir = null) {
    if ($outputDir === null) {
        $outputDir = __DIR__ . '/output/';
    }
    
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    $filepath = $outputDir . "post-{$id}-{$lang}.png";
    $charCount = mb_strlen(strip_tags($content), 'UTF-8');
    $titleHash = md5($title . $id);
    $contentHash = sha1($content . $id);

    // --- COLOR PALETTE based on content length ---
    $colors = [
        [200, 170, 100],  // gold (< 2000 chars)
        [100, 170, 200],  // cool blue (2000-4000)
        [170, 100, 200],  // purple (4000-6000)
        [200, 120, 100],  // terracotta (6000-8000)
        [100, 200, 150],  // mint (8000+)
    ];
    $colorIndex = min(4, intdiv($charCount, 2000));
    $accent = $colors[$colorIndex];

    // --- CREATE IMAGE ---
    $img = imagecreatetruecolor(IMG_SIZE, IMG_SIZE);
    imagesavealpha($img, true);

    $bgColor = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $bgColor);

    // --- BACKGROUND GRID ---
    $gridColor = imagecolorallocatealpha($img, 0, 0, 0, 120);
    for ($x = 0; $x <= IMG_SIZE; $x += CUBE_SIZE) {
        imageline($img, (int)$x, 0, (int)$x, IMG_SIZE, $gridColor);
    }
    for ($y = 0; $y <= IMG_SIZE; $y += CUBE_SIZE) {
        imageline($img, 0, (int)$y, IMG_SIZE, (int)$y, $gridColor);
    }

    // --- GENERATE PATTERN ---
    $fullHash = $titleHash . $contentHash . md5($charCount . $id);
    $expandedHash = '';
    for ($i = 0; $i < 20; $i++) {
        $expandedHash .= md5($fullHash . $i);
    }

    $hashIndex = 0;
    for ($row = 0; $row < GRID_COLS; $row++) {
        for ($col = 0; $col < GRID_COLS; $col++) {
            $hashChar = $expandedHash[$hashIndex % strlen($expandedHash)];
            $hashVal = hexdec($hashChar);
            $hashIndex++;

            $x1 = (int)($col * CUBE_SIZE);
            $y1 = (int)($row * CUBE_SIZE);
            $margin = 2;
            $cx1 = $x1 + $margin;
            $cy1 = $y1 + $margin;
            $cx2 = (int)($x1 + CUBE_SIZE - 1) - $margin;
            $cy2 = (int)($y1 + CUBE_SIZE - 1) - $margin;

            if ($hashVal >= 10) {
                $brightness = 20 + ($hashVal - 10) * 8;
                $cubeColor = imagecolorallocate($img, $brightness, $brightness, $brightness);
                imagefilledrectangle($img, $cx1, $cy1, $cx2, $cy2, $cubeColor);
            } elseif ($hashVal >= 7) {
                $cubeColor = imagecolorallocate($img, (int)($accent[0] * 0.7), (int)($accent[1] * 0.7), (int)($accent[2] * 0.7));
                imagefilledrectangle($img, $cx1, $cy1, $cx2, $cy2, $cubeColor);
            } elseif ($hashVal >= 5) {
                $cubeColor = imagecolorallocate($img, 230, 230, 230);
                imagefilledrectangle($img, $cx1, $cy1, $cx2, $cy2, $cubeColor);
            }
        }
    }

    // --- CENTER ZONE ---
    $centerStart = (GRID_COLS / 2) - 3;
    $centerEnd = (GRID_COLS / 2) + 3;
    for ($row = $centerStart; $row < $centerEnd; $row++) {
        for ($col = $centerStart; $col < $centerEnd; $col++) {
            $x1 = (int)($col * CUBE_SIZE) + 2;
            $y1 = (int)($row * CUBE_SIZE) + 2;
            $x2 = (int)(($col + 1) * CUBE_SIZE) - 3;
            $y2 = (int)(($row + 1) * CUBE_SIZE) - 3;
            $whiteBg = imagecolorallocate($img, 255, 255, 255);
            imagefilledrectangle($img, $x1, $y1, $x2, $y2, $whiteBg);
        }
    }

    // --- CENTER CHARACTER ---
    $centerX = IMG_SIZE / 2;
    $centerY = IMG_SIZE / 2;

    if (CENTER_CHAR !== '') {
        $fontPaths = [
            '/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc',
            '/usr/share/fonts/truetype/noto/NotoSansCJK-Regular.ttc',
            '/usr/share/fonts/noto-cjk/NotoSansCJK-Regular.ttc',
            '/usr/share/fonts/google-noto-cjk/NotoSansCJK-Regular.ttc',
            '/usr/share/fonts/OTF/NotoSansCJK-Regular.ttc',
            __DIR__ . '/fonts/NotoSansCJK-Regular.ttc',
            __DIR__ . '/fonts/NotoSansJP-Regular.otf',
        ];

        $font = null;
        foreach ($fontPaths as $fp) {
            if (file_exists($fp)) { $font = $fp; break; }
        }

        if ($font) {
            $kanjiColor = imagecolorallocate($img, (int)($accent[0] * 0.8), (int)($accent[1] * 0.8), (int)($accent[2] * 0.8));
            $fontSize = CUBE_SIZE * 4;
            $bbox = imagettfbbox($fontSize, 0, $font, CENTER_CHAR);
            $textW = $bbox[2] - $bbox[0];
            $textH = $bbox[1] - $bbox[7];
            imagettftext($img, $fontSize, 0, (int)($centerX - $textW / 2), (int)($centerY + $textH / 2), $kanjiColor, $font, CENTER_CHAR);
        } else {
            // Fallback: diamond shape
            $kanjiColor = imagecolorallocate($img, (int)($accent[0] * 0.7), (int)($accent[1] * 0.7), (int)($accent[2] * 0.7));
            $ds = (int)(CUBE_SIZE * 2.5);
            $cx = (int)$centerX;
            $cy = (int)$centerY;
            $points = [$cx, $cy - $ds, $cx + $ds, $cy, $cx, $cy + $ds, $cx - $ds, $cy];
            imagepolygon($img, $points, 4, $kanjiColor);
        }
    }

    // --- ACCENT DOTS ---
    $dotSeed = crc32($titleHash);
    mt_srand($dotSeed);
    for ($i = 0; $i < 12; $i++) {
        $dx = mt_rand(50, IMG_SIZE - 50);
        $dy = mt_rand(50, IMG_SIZE - 50);
        $dr = mt_rand(2, 4);
        $dotColor = imagecolorallocate($img, (int)($accent[0] * 0.8), (int)($accent[1] * 0.8), (int)($accent[2] * 0.8));
        imagefilledellipse($img, $dx, $dy, $dr * 2, $dr * 2, $dotColor);
    }

    // --- BORDER ---
    $borderColor = imagecolorallocate($img, (int)($accent[0] * 0.5), (int)($accent[1] * 0.5), (int)($accent[2] * 0.5));
    imagerectangle($img, 0, 0, IMG_SIZE - 1, IMG_SIZE - 1, $borderColor);

    // --- SAVE ---
    imagepng($img, $filepath, 7);
    imagedestroy($img);

    return $filepath;
}

/**
 * Generate an animated SVG from title and content
 * Same visual logic as PNG but with CSS animations
 */
function generateSVG($id, $title, $content, $lang = 'en', $outputDir = null) {
    if ($outputDir === null) {
        $outputDir = __DIR__ . '/output/';
    }
    
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    $filepath = $outputDir . "post-{$id}-{$lang}.svg";
    $charCount = mb_strlen(strip_tags($content), 'UTF-8');
    $titleHash = md5($title . $id);
    $contentHash = sha1($content . $id);

    $colors = [
        [200, 170, 100], [100, 170, 200], [170, 100, 200],
        [200, 120, 100], [100, 200, 150],
    ];
    $colorIndex = min(4, intdiv($charCount, 2000));
    $accent = $colors[$colorIndex];

    $gridSize = 32;
    $cubeSize = 37.5;
    $margin = 2;
    $imgSize = 1200;

    $fullHash = $titleHash . $contentHash . md5($charCount . $id);
    $expandedHash = '';
    for ($i = 0; $i < 20; $i++) {
        $expandedHash .= md5($fullHash . $i);
    }

    $centerStart = ($gridSize / 2) - 3;
    $centerEnd = ($gridSize / 2) + 3;

    // Blink cells
    $blinkSeed = crc32($titleHash . 'blink');
    mt_srand($blinkSeed);
    $blinkCells = [];
    for ($i = 0; $i < 8; $i++) {
        $blinkCells[] = [mt_rand(0, $gridSize - 1), mt_rand(0, $gridSize - 1)];
    }

    $rects = '';
    $hashIndex = 0;

    for ($row = 0; $row < $gridSize; $row++) {
        for ($col = 0; $col < $gridSize; $col++) {
            $hashChar = $expandedHash[$hashIndex % strlen($expandedHash)];
            $hashVal = hexdec($hashChar);
            $hashIndex++;

            if ($row >= $centerStart && $row < $centerEnd && $col >= $centerStart && $col < $centerEnd) continue;

            $x = round($col * $cubeSize + $margin, 1);
            $y = round($row * $cubeSize + $margin, 1);
            $w = round($cubeSize - $margin * 2, 1);
            $h = round($cubeSize - $margin * 2, 1);

            $fill = '';
            if ($hashVal >= 10) {
                $b = 20 + ($hashVal - 10) * 8;
                $fill = "rgb({$b},{$b},{$b})";
            } elseif ($hashVal >= 7) {
                $r = (int)($accent[0] * 0.7); $g = (int)($accent[1] * 0.7); $bl = (int)($accent[2] * 0.7);
                $fill = "rgb({$r},{$g},{$bl})";
            } elseif ($hashVal >= 5) {
                $fill = 'rgb(230,230,230)';
            }

            if ($fill) {
                $distX = abs($col - $gridSize / 2);
                $distY = abs($row - $gridSize / 2);
                $dist = sqrt($distX * $distX + $distY * $distY);
                $delay = round($dist * 0.03, 2);

                $cellClass = 'cell';
                foreach ($blinkCells as $bi => $bc) {
                    if ($bc[0] === $col && $bc[1] === $row) {
                        $cellClass = 'blink blink-' . ($bi % 3);
                        break;
                    }
                }
                $rects .= "    <rect x=\"{$x}\" y=\"{$y}\" width=\"{$w}\" height=\"{$h}\" fill=\"{$fill}\" class=\"{$cellClass}\" style=\"--d:{$delay}s\"/>\n";
            }
        }
    }

    // Accent dots
    $dotSeed = crc32($titleHash);
    mt_srand($dotSeed);
    $dots = '';
    for ($i = 0; $i < 12; $i++) {
        $dx = mt_rand(50, $imgSize - 50); $dy = mt_rand(50, $imgSize - 50); $dr = mt_rand(2, 4);
        $r = (int)($accent[0] * 0.8); $g = (int)($accent[1] * 0.8); $bl = (int)($accent[2] * 0.8);
        $dots .= "    <circle cx=\"{$dx}\" cy=\"{$dy}\" r=\"{$dr}\" fill=\"rgb({$r},{$g},{$bl})\"/>\n";
    }

    $kanjiR = (int)($accent[0] * 0.8); $kanjiG = (int)($accent[1] * 0.8); $kanjiB = (int)($accent[2] * 0.8);
    $centerPx = $imgSize / 2;
    $borderR = (int)($accent[0] * 0.5); $borderG = (int)($accent[1] * 0.5); $borderB = (int)($accent[2] * 0.5);
    $char = CENTER_CHAR;

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$imgSize} {$imgSize}" width="{$imgSize}" height="{$imgSize}">
  <style>
    rect.cell { opacity: 0; animation: cellIn 0.6s ease var(--d, 0s) forwards; }
    .blink-0 { animation: cellIn 0.6s ease var(--d, 0s) forwards, pulse0 3.5s ease-in-out calc(var(--d, 0s) + 1.2s) infinite; }
    .blink-1 { animation: cellIn 0.6s ease var(--d, 0s) forwards, pulse1 5.5s ease-in-out calc(var(--d, 0s) + 1.2s) infinite; }
    .blink-2 { animation: cellIn 0.6s ease var(--d, 0s) forwards, pulse2 4s ease-in-out calc(var(--d, 0s) + 1.2s) infinite; }
    @keyframes cellIn { 0% { opacity: 0; } 100% { opacity: 1; } }
    @keyframes pulse0 { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
    @keyframes pulse1 { 0%, 100% { opacity: 0.9; } 50% { opacity: 0.2; } }
    @keyframes pulse2 { 0%, 100% { opacity: 1; } 40% { opacity: 0.15; } }
  </style>
  <rect width="{$imgSize}" height="{$imgSize}" fill="white"/>
{$rects}
{$dots}
  <text x="{$centerPx}" y="{$centerPx}" text-anchor="middle" dominant-baseline="central" font-size="150" font-family="'Noto Sans CJK JP', 'Hiragino Kaku Gothic Pro', sans-serif" fill="rgb({$kanjiR},{$kanjiG},{$kanjiB})" opacity="0" style="animation: cellIn 0.6s ease 0.8s forwards">{$char}</text>
  <rect x="0" y="0" width="{$imgSize}" height="{$imgSize}" fill="none" stroke="rgb({$borderR},{$borderG},{$borderB})" stroke-width="1"/>
</svg>
SVG;

    file_put_contents($filepath, $svg);
    return $filepath;
}

// === CLI USAGE ===
if (php_sapi_name() === 'cli') {
    $opts = getopt('', ['title:', 'content:', 'id:', 'lang:', 'format:', 'output:']);
    
    $title = $opts['title'] ?? 'Untitled';
    $content = $opts['content'] ?? $title;
    $id = (int)($opts['id'] ?? 1);
    $lang = $opts['lang'] ?? 'en';
    $format = $opts['format'] ?? 'png';
    $outputDir = ($opts['output'] ?? __DIR__ . '/output/');
    if (substr($outputDir, -1) !== '/') $outputDir .= '/';
    
    if ($format === 'svg') {
        $path = generateSVG($id, $title, $content, $lang, $outputDir);
    } else {
        $path = generateImage($id, $title, $content, $lang, $outputDir);
    }
    
    echo "Generated: {$path}\n";
}
