<?php

class ImageFetch {

  public $imagePath;
  public $localPath;
  public $imageData;
  public $contentType;

  public function __construct($path) {
    $this->imagePath = $path;
    $this->localPath = LOCAL_PATH . $path;
  }

  /**
   * Attempts to fetch the image from the defined sources
   * @return {boolean} Whether the fetch was successful or not
   */
  public function fetch() {
    // Never thought I'd use this again...
    global $IMAGE_SOT;

    // Loop through all sources and attempt a fetch
    // Bail on the first success
    foreach ($IMAGE_SOT as $source) {
      if ($this->fetchFromSource($source)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Saves the current image data to the output path
   */
  public function save() {
    if ($this->imageData) {
      $this->prepDirectoryPath($this->localPath);
      file_put_contents($this->localPath, $this->imageData);
    }
  }

  private function fetchFromSource($source) {
    $retVal = false;

    // Verify the image is okay first
    $url = $source . $this->imagePath;
    $headers = @get_headers($url, 1);

    if ($headers && strpos($headers[0], '200') !== false && (int) $headers['Content-Length'] > 0) {
      $data = @file_get_contents($url);
      if ($data) {
        $this->imageData = $data;
        $this->contentType = $headers['Content-Type'];
        $retVal = true;
      }
    }

    return $retVal;
  }

  /**
   * Creates all directories of a file path as needed
   */
  protected function prepDirectoryPath($path) {
    $path = pathinfo($path);
    if (!file_exists($path['dirname'])) {
      mkdir(LOCAL_PATH . implode('/', $path), 777, true);
    }
  }
}