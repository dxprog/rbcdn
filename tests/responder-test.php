<?php

use PHPUnit\Framework\TestCase;

final class ResponderTest extends TestCase {

  private $storagePath;
  private $responder;
  private $outputWriter;

  function setUp() {
    $tmpDir = tempnam(sys_get_temp_dir(), 'rbcdn-tests');

    // Delete the created file and replace it with a directory
    unlink($tmpDir);
    mkdir($tmpDir, 0777, true);
    $this->storagePath = $tmpDir;

    $this->responder = new Responder();
    $this->outputWriter = \OutputWriter::getInstance();
    $this->outputWriter->reset();
  }

  function tearDown() {
    exec('rm -rf ' . $this->storagePath);
  }

  function createResponderConfig($imagePath, $method = 'GET') {
    return (object)[
      'sources' => SOURCES,
      'localPath' => $this->storagePath,
      'server' => [ 'REQUEST_METHOD' => $method ],
      'queryString' => [ '_q' => $imagePath ]
    ];
  }

  function testFetchesAndSendsDesktopImage() {
    $this->responder->processRequest($this->createResponderConfig(SOURCE_2_JPG));
    $this->assertEquals($this->outputWriter->headers['Content-Type'], 'image/jpeg', 'the wrong Content-Type was sent');
    $this->assertEquals(md5($this->outputWriter->output), md5_file(SOURCES[1] . SOURCE_2_JPG), 'the wrong output was sent');
  }

  function testFetchesAndSendsMobileImage() {
    $this->responder->processRequest($this->createResponderConfig('/m' . SOURCE_1_PNG));
    $this->assertEquals($this->outputWriter->headers['Content-Type'], 'image/jpeg', 'the wrong Content-Type was sent');
    $this->assertTrue(\ImageAssert::assertBufferDimensions($this->outputWriter->output, 1080, 1010));
  }

  function testSends404WhenImageNotFound() {
    $this->responder->processRequest($this->createResponderConfig('/some-made-up-nonsense.jpg'));
    $this->assertTrue(isset($this->outputWriter->headers['HTTP/1.1 404 Not Found']), 'the 404 header was not written');
    $this->assertTrue($this->outputWriter->responseEnded, 'the response was not ended');
  }

  function testSends404OnHeadRequest() {
    $this->responder->processRequest($this->createResponderConfig(SOURCE_1_PNG, 'HEAD'));
    $this->assertTrue(isset($this->outputWriter->headers['HTTP/1.1 404 Not Found']), 'the 404 header was not written');
    $this->assertTrue($this->outputWriter->responseEnded, 'the response was not ended');
  }
}