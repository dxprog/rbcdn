<?php

use PHPUnit\Framework\TestCase;

final class MobileFetchTest extends TestCase {

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
    $instance = new MobileFetch(SOURCE_1_PNG, SOURCES, $this->storagePath);
    $this->assertTrue($instance->fetch(), 'image was not found');
    $this->assertEquals($instance->contentType, 'image/png', 'Content-Type is different from disk');
    $this->assertEquals(md5($instance->imageData), md5_file(SOURCES[0] . SOURCE_1_PNG), 'loaded image data different from disk');
  }

  function testFetchesFromSecondSource() {
    $instance = new MobileFetch(SOURCE_2_JPG, SOURCES, $this->storagePath);
    $this->assertTrue($instance->fetch(), 'image was not found');
    $this->assertEquals($instance->contentType, 'image/jpeg', 'Content-Type is different from disk');
    $this->assertEquals(md5($instance->imageData), md5_file(SOURCES[1] . SOURCE_2_JPG), 'loaded image data different from disk');
  }

  function testReturnsFalseForUnfoundImage() {
    $instance = new MobileFetch('/some-mage-up-nonsense.jpg', SOURCES, $this->storagePath);
    $this->assertFalse($instance->fetch(), 'image marked as found when file does not exist');
  }

  function testSavesResizedLargeImage() {
    $instance = new MobileFetch(SOURCE_1_PNG, SOURCES, $this->storagePath);
    if ($instance->fetch()) {
      $instance->save();
      $savedMobileImage = $this->storagePath . '/m' . SOURCE_1_PNG;
      $this->assertFileEquals(SOURCES[0] . SOURCE_1_PNG, $this->storagePath . SOURCE_1_PNG, 'desktop image data differs from source');
      $this->assertFileNotEquals(SOURCES[0] . SOURCE_1_PNG, $savedMobileImage, 'resized mobile image data differs from source');

      // Verify the image size and content-type
      $mobileImageInfo = exec('file ' . $savedMobileImage);
      $this->assertTrue(strpos($mobileImageInfo, '1080x1010') !== false, 'the resized image has incorrect dimensions');
      $this->assertEquals(mime_content_type($savedMobileImage), 'image/jpeg', 'the image was not saved as a JPEG');
    } else {
      $this->assertTrue(false, 'unable to load first source image');
    }
  }

  function testSavesNonResizeSmallImageAndCreatesSymlink() {
    $instance = new MobileFetch(SOURCE_2_JPG, SOURCES, $this->storagePath);
    if ($instance->fetch()) {
      $instance->save();
      $savedMobileImage = $this->storagePath . '/m' . SOURCE_2_JPG;
      $this->assertFileEquals(SOURCES[1] . SOURCE_2_JPG, $this->storagePath . SOURCE_2_JPG, 'desktop image data differs from source');
      $this->assertFileEquals(SOURCES[1] . SOURCE_2_JPG, $savedMobileImage, 'non-resized mobile image data differs from source');
      $this->assertTrue(is_link($savedMobileImage), 'the mobile image was not symlinked to the desktop image');
    } else {
      $this->assertTrue(false, 'unable to load second source image');
    }
  }

  function testDoesNotResizeLargeGifAndCreatesSymlink() {
    $instance = new MobileFetch(SOURCE_1_GIF, SOURCES, $this->storagePath);
    if ($instance->fetch()) {
      $instance->save();
      $savedMobileImage = $this->storagePath . '/m' . SOURCE_1_GIF;
      $this->assertFileEquals(SOURCES[0] . SOURCE_1_GIF, $this->storagePath . SOURCE_1_GIF, 'desktop image data differs from source');
      $this->assertFileEquals(SOURCES[0] . SOURCE_1_GIF, $savedMobileImage, 'non-resized mobile image data differs from source');
      $this->assertTrue(is_link($savedMobileImage), 'the mobile image was not symlinked to the desktop image');
    } else {
      $this->assertTrue(false, 'unable to load second source image');
    }
  }

  function testLoadsExistingFileFromStorage() {
    $fakeImage = '/not-the-real-slim-shady.png';
    $fakeImaeData = 'This is not the image you are looking for';
    file_put_contents($this->storagePath . $fakeImage, $fakeImage);

    $instance = new MobileFetch($fakeImage, SOURCES, $this->storagePath);
    $this->assertTrue($instance->fetch(), 'image was not found');
    $this->assertEquals($instance->contentType, 'text/plain', 'Content-Type is different from disk');
    $this->assertEquals(md5($instance->imageData), md5_file($this->storagePath . $fakeImage), 'loaded image data different from disk');
  }
}