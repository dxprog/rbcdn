<?php

class ImageAssert {
  public static function assertFileDimensions($file, $width, $height) {
    $type = mime_content_type($file);
    $img = null;
    switch ($type) {
      case 'image/jpg':
      case 'image/jpeg':
        $img = imagecreatefromjpeg($file);
        break;
      case 'image/png':
        $img = imagecreatefrompng($file);
        break;
      case 'image/gif':
        $img = imagecreatefromgif($file);
        break;
    }

    $message = [];
    $retVal = true;

    if ($img) {
      $actualWidth = imagesx($img);
      $actualHeight = imagesy($img);
      imagedestroy($img);

      if ($actualWidth !== $width) {
        $message[] = 'Widths differ; expected ' . $width . ', got ' . $actualWidth;
        $retVal = false;
      }

      if ($actualHeight !== $height) {
        $message[] = 'Heights differ; hxpected ' . $height . ', got ' . $actualHeight;
        $retVal = false;
      }

    } else {
      $message[] = 'Unable to load image ' . $file;
      $retVal = false;
    }

    if (count($message)) {
      echo PHP_EOL, implode(PHP_EOL, $message), PHP_EOL;
    }

    return $retVal;
  }

  public static function assertBufferDimensions($buffer, $width, $height) {
    $file = tempnam(sys_get_temp_dir(), 'rbcdn-test');
    file_put_contents($file, $buffer);
    $retVal = self::assertFileDimensions($file, $width, $height);
    unlink($file);
    return $retVal;
  }
}