<?php

use PHPUnit\Framework\TestCase;

final class ImageFetchTest extends TestCase {

  private $storagePath;

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
    $instance = new ImageFetch(SOURCE_1_PNG, SOURCES, $this->storagePath);
    $this->assertTrue($instance->fetch(), 'image was not found');
    $this->assertEquals($instance->contentType, 'image/png', 'Content-Type is different from disk');
    $this->assertEquals(md5($instance->imageData), md5_file(SOURCES[0] . SOURCE_1_PNG), 'loaded image data different from disk');
  }

  function testFetchesFromSecondSource() {
    $instance = new ImageFetch(SOURCE_2_JPG, SOURCES, $this->storagePath);
    $this->assertTrue($instance->fetch(), 'image was not found');
    $this->assertEquals($instance->contentType, 'image/jpeg', 'Content-Type is different from disk');
    $this->assertEquals(md5($instance->imageData), md5_file(SOURCES[1] . SOURCE_2_JPG), 'loaded image data different from disk');
  }

  function testReturnsFalseForUnfoundImage() {
    $instance = new ImageFetch('/some-made-up-nonsense.jpg', SOURCES, $this->storagePath);
    $this->assertFalse($instance->fetch(), 'image marked as found when file does not exist');
  }

  function testSavesTheFetchedImage() {
    $instance = new ImageFetch(SOURCE_1_PNG, SOURCES, $this->storagePath);
    if ($instance->fetch()) {
      $instance->save();
      $this->assertEquals(md5_file(SOURCES[0] . SOURCE_1_PNG), md5_file($this->storagePath . SOURCE_1_PNG), 'saved image data differs from source');
    } else {
      $this->assertTrue(false, 'unable to load first source image');
    }
  }

  function testSavesTheFetchedImageWithPath() {
    $instance = new ImageFetch(SOURCE_2_JPG, SOURCES, $this->storagePath);
    if ($instance->fetch()) {
      $instance->save();
      $this->assertEquals(md5_file(SOURCES[1] . SOURCE_2_JPG), md5_file($this->storagePath . SOURCE_2_JPG), 'saved image data differs from source');
    } else {
      $this->assertTrue(false, 'unable to load second source image');
    }
  }
}