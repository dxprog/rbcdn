<?php

// Defines the maximum width of an image for mobile.
// Width is targeted as most phone viewing happens
// while in portrait mode, so that'd be the theoretical
// widest an image could be. 1080 seemed like a reasonable
// number to settle on since many phones are 1080p friendly
define('MOBILE_MAX_WIDTH', 1080);

class MobileFetch extends ImageFetch {

  private $hasLocalCopy = false;
  private $baseStoragePath;

  /**
   * ImageFetch constructor
   *
   * @param string $path The path to the file to be fetched
   * @param string[] $sources The array of sources to scan for the file
   * @param string $storagePath The path to save files. Must have trailing slash
   */
  public function __construct($path, array $sources, $storagePath) {
    parent::__construct($path, $sources, $storagePath);
    $this->baseStoragePath = $storagePath;
  }

  public function fetch() {
    // First, check to see if there's a normal version of the image
    // already fetched.
    if (file_exists($this->storagePath)) {
      $this->contentType = mime_content_type($this->storagePath);
      $this->imageData = file_get_contents($this->storagePath);
      $this->hasLocalCopy = true;
      return true;
    } else {
      return parent::fetch();
    }
  }

  /**
   * Saves the image data with a mobile resize attempt
   */
  public function save() {
    if ($this->imageData) {
      // If we were unable to source this locally, be optimistic
      // and save the full version as well.
      if (!$this->hasLocalCopy) {
        parent::save();
      }

      $mobilePath = $this->baseStoragePath . '/m' . $this->imagePath;
      $this->prepDirectoryPath($mobilePath);

      // Attempt to do a resize
      $tmpFile = tempnam(sys_get_temp_dir(), 'rbmobile');
      $resizedImage = $this->resizeImage($tmpFile);
      unlink($tmpFile);
      if ($resizedImage) {
        file_put_contents($mobilePath, $resizedImage);

        // Replace all the outputs for this particular instance
        $this->imageData = $resizedImage;
        $this->contentType = 'image/jpeg';
      } else {
        // If nothing was done to the original file, just create
        // a symlink to it so we can save some space
        symlink(realpath($this->storagePath), $mobilePath);
      }
    }
  }

  /**
  * Resizes an image for mobile optimization
  * @param string $tmpFile The path to a file to perform temporary operations
  * @return mixed The resized image buffer if the resize was successful, null if nothing was done
  */
  private function resizeImage($tmpFile) {
    $retVal = null;

    // Write the current buffer to the provided temp file
    // so we can load it into a gd2 buffer
    file_put_contents($tmpFile, $this->imageData);

    // Only attempt to process jpegs and pngs. Anything else
    // can fall through.
    $img = null;
    switch ($this->contentType) {
      case 'image/jpeg':
      case 'image/jpg':
        $img = @imagecreatefromjpeg($tmpFile);
        break;
      case 'image/png':
        $img = @imagecreatefrompng($tmpFile);
        break;
    }

    if ($img) {
      // If the image is wider than the max, then resize. Otherwise, let it through
      $width = imagesx($img);
      if ($width > MOBILE_MAX_WIDTH) {
        // Calculate the new size
        $height = imagesy($img);
        $ratio = $height / $width;
        $dstWidth = MOBILE_MAX_WIDTH;
        $dstHeight = round($dstWidth * $ratio);

        // Create and resample the image
        $resizedImg = imagecreatetruecolor($dstWidth, $dstHeight);
        imagecopyresampled($resizedImg, $img, 0, 0, 0, 0, $dstWidth, $dstHeight, $width, $height);

        // Save the image as a JPEG reusing the existing temp file
        imagejpeg($resizedImg, $tmpFile, 80);
        $retVal = file_get_contents($tmpFile);

        // Clean up the resized image
        imagedestroy($resizedImg);
      }

      // Clean up the original image
      imagedestroy($img);
    }

    return $retVal;
  }

}