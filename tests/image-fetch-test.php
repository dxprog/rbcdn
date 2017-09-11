<?php

use PHPUnit\Framework\TestCase;

final class ImageFetchTest extends TestCase {
  static $sources = [
    './tests/sources/chitoge.cdn.awwni.me',
    './tests/sources/taiga.cdn.awwni.me'
  ];

  private $storagePath;

  // First source has this image
  const IMAGE_1 = '/anime1.png';

  // Second source has this image
  const IMAGE_2 = '/tsunderes/anime2.jpg';

  function setUp() {
    $tmpDir = tempnam(sys_get_temp_dir(), 'rbcdn-tests');

    // Delete the created file and replace it with a directory
    unlink($tmpDir);
    mkdir($tmpDir, 0777, true);
    $this->storagePath = $tmpDir;
  }

  function tearDown() {
    exec('rm -rf ' . $this->storagePath);
  }

  function testFetchesFromFirstSource() {
    $instance = new ImageFetch(self::IMAGE_1, self::$sources, $this->storagePath);
    $this->assertTrue($instance->fetch(), 'image was not found');
    $this->assertEquals($instance->contentType, 'image/png', 'Content-Type is different from disk');
    $this->assertEquals(md5($instance->imageData), md5_file(self::$sources[0] . self::IMAGE_1), 'loaded image data different from disk');
  }

  function testFetchesFromSecondSource() {
    $instance = new ImageFetch(self::IMAGE_2, self::$sources, $this->storagePath);
    $this->assertTrue($instance->fetch(), 'image was not found');
    $this->assertEquals($instance->contentType, 'image/jpeg', 'Content-Type is different from disk');
    $this->assertEquals(md5($instance->imageData), md5_file(self::$sources[1] . self::IMAGE_2), 'loaded image data different from disk');
  }

  function testReturnsFalseForUnfoundImage() {
    $instance = new ImageFetch('/some-mage-up-nonsense.jpg', self::$sources, $this->storagePath);
    $this->assertFalse($instance->fetch(), 'image marked as found when file does not exist');
  }

  function testSavesTheFetchedImage() {
    $instance = new ImageFetch(self::IMAGE_1, self::$sources, $this->storagePath);
    if ($instance->fetch()) {
      $instance->save();
      $this->assertEquals(md5($instance->imageData), md5_file($this->storagePath . self::IMAGE_1), 'saved image data differs from source');
    } else {
      $this->assertTrue(false, 'unable to load first source image');
    }
  }

  function testSavesTheFetchedImageWithPath() {
    $instance = new ImageFetch(self::IMAGE_2, self::$sources, $this->storagePath);
    if ($instance->fetch()) {
      $instance->save();
      $this->assertEquals(md5($instance->imageData), md5_file($this->storagePath . self::IMAGE_2), 'saved image data differs from source');
    } else {
      $this->assertTrue(false, 'unable to load second source image');
    }
  }
}