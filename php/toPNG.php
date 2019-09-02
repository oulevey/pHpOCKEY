<?php
// fading function
function fade($range, $steps, $t, $modifier) {
  return $range - round($range * ( (cos(($t / $steps) * pi() ) + 1) / $modifier));
}

function drawLine($image, $x, $y, $dir, $len) {
  $_x = $x;
  $_y = $y;
  for ($i = 5; $i <= $len; $i++) {
    // increment x and y
    if ($dir === '+h') {
      $x = $_x + $i;
    } elseif ($dir === '-h') {
      $x = $_x - $i;
    } elseif ($dir === '+v') {
      $y = $_y + $i;
    } else/*if ($dir === '-v')*/ {
      $y = $_y - $i;
    }
    imagealphablending($image, false);
    $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagesetpixel($image, $x, $y, $color);
    imagealphablending($image, true);
    // get the pixel color from the diagonal
    $rgba = @imagecolorat($image, $x + 1, $y + 1);
    if ($rgba) {
      $alpha = ($rgba & 0x7F000000) >> 24;
      $color = imagecolorallocatealpha($image, 0, 0, 0, $alpha);
      imagesetpixel($image, $x, $y, $color);
    }
  }
}

// make heatpoint image
function makeHeatPoint($radius, $modifier = 10) {
  // calculate diameter
  $diameter = $radius * 2;

  // position
  $x = $y = $radius;

  // create image base
  $image = imagecreatetruecolor($diameter, $diameter);

  // set transparent color
  $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);

  // transparent background
  imagefill($image, 0, 0, $transparent);

  // build heatpoint
  for ($i = 0; $i <= $radius; $i++) {
    $size  = 2 * $i;
    $alpha = fade(127, $radius, $i, $modifier);
    $color = imagecolorallocatealpha($image, 0, 0, 0, $alpha);
    imagearc($image, $x, $y, $size, $size, 0, 360, $color);
    imagesetpixel($image, $x + $i, $y, $transparent);
    imagesetpixel($image, $x - $i, $y, $transparent);
    imagesetpixel($image, $x, $y + $i, $transparent);
    imagesetpixel($image, $x, $y - $i, $transparent);
  }

  // fix the dark cross
  drawLine($image, $radius, $y, '+h', $radius);
  drawLine($image, $radius, $y, '-h', $radius);
  drawLine($image, $radius, $y, '+v', $radius);
  drawLine($image, $radius, $y, '-v', $radius);

  return $image;
}

// return the biggest value
function getMaxValue($matrix) {
  $max = 0;
  foreach ($matrix as $y => $row) {
    foreach ($row as $x => $value) {
      $max = max($max, $value);
    }
  }
  return $max;
}

// map a value from range [start, end] to [start, end]
function map($value, $from, $to) {
  $scale = ($to[0] - $to[1]) / ($from[0] - $from[1]);
  return (($value - $from[1]) * $scale) + $to[1];
}

// convert a matrix to PNG file
function toPNG($path, $matrix, $radius = 42, $modifier = 15) {
  // background image
  $backgroundFile = ROOT_PATH . "/imgs/background.png";

  // create base image from the background image
  $background = imagecreatefrompng($backgroundFile);

  // get image size
  $width  = imagesx($background);
  $height = imagesy($background);

  // calculate scale
  $scale = round($width / MATRIX_COLS);

  // create image base
  $image = imagecreatetruecolor($width, $height);
  $cloud = imagecreatetruecolor($width, $height);

  // allocate a white color and fill the new image with it
  $white       = imagecolorallocate($image, 255, 255, 255);
  $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
  imagefill($image, 0, 0, $white);
  imagefill($cloud, 0, 0, $transparent);

  // copy the background
  imagecopy($image, $background, 0, 0, 0, 0, $width, $height);

  // fill the image with heatpoints
  $heatpoint = makeHeatPoint($radius, $modifier);
  $diameter  = $radius * 2;

  foreach ($matrix as $y => $row) {
    foreach ($row as $x => $value) {
      if ($value <= 0) continue;
      imagecopyresampled(
        $cloud, $heatpoint,
        ($x * $scale) - $radius, ($y * $scale) - $radius,
        0, 0,
        $diameter, $diameter,
        $diameter, $diameter
      );
    }
  }

  // colorization pixel by pixel
  for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
      // get the pixel color
      $rgba  = imagecolorat($cloud, $x, $y);
      $alpha = ($rgba & 0x7F000000) >> 24;
      if ($alpha === 127) continue;
      // update color
      $a = map($alpha, [0, 127], [0, 255]);
      $r = 255 - $a;
      $g = 0;
      $b = $a;
      $color = imagecolorallocatealpha($cloud, $r, $g, $b, $alpha);
      imagesetpixel($cloud, $x, $y, $color);
    }
  }

  // copy the background
  imagecopy($image, $background, 0, 0, 0, 0, $width, $height);
  imagecopy($image, $cloud, 0, 0, 0, 0, $width, $height);

  // save image
  imagepng($image, $path);

  // cleanning
  imagedestroy($background);
  imagedestroy($image);
  imagedestroy($cloud);
}
