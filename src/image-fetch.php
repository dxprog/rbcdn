<?php

class ImageFetch {

  public $imagePath;
  public $storagePath;
  public $imageData;
  public $contentType;

  private $_sources;

  /**
   * ImageFetch constructor
   *
   * @param string $path The path to the file to be fetched
   * @param string[] $sources The array of sources to scan for the file
   * @param string $storagePath The path to save files. Must have trailing slash
   */
  public function __construct($path, array $sources, $storagePath) {
    $this->imagePath = $path;
    $this->storagePath = $storagePath . $path;
    $this->_sources = $sources;
  }

  /**
   * Attempts to fetch the image from the defined sources
   * @return {boolean} Whether the fetch was successful or not
   */
  public function fetch() {
    // Loop through all sources and attempt a fetch
    // Bail on the first success
    foreach ($this->_sources as $source) {
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
      $this->prepDirectoryPath($this->storagePath);
      file_put_contents($this->storagePath, $this->imageData);
    }
  }

  private function fetchFromSource($source) {
    $retVal = false;

    // Verify the image is okay first
    $url = $source . $this->imagePath;
    $headers = getHeaders($url);

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
    $pathInfo = pathinfo($path);
    if (!is_dir($pathInfo['dirname'])) {
      mkdir($pathInfo['dirname'], 0777, true);
    }
  }
}