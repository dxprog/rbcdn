<?php

require('config.php');

// Defines the maximum width of an image for mobile.
// Width is targeted as most phone viewing happens
// while in portrait mode, so that'd be the theoretical
// widest an image could be. 1080 seemed like a reasonable
// number to settle on since many phones are 1080p friendly
define('MOBILE_MAX_WIDTH', 1080);

function send404() {
    header('HTTP/1.1 404 Not Found');
    exit;
}

function prepDirectoryPath($file) {

    $path = explode('/', $file);

    // Pop off the file name and rebuild the full path
    array_pop($path);
    $fullPath = '';

    // If the directory already exists, bail now
    if (!is_dir(LOCAL_PATH . '/' . implode('/', $path))) {

        // Verify and create each section as necessary
        foreach ($path as $directory) {
            if (strlen($directory) > 0) {
                $fullPath .= '/' . $directory;
                if (!is_dir(LOCAL_PATH . '/' . $fullPath)) {
                    mkdir(LOCAL_PATH . '/' . $fullPath);
                }
            }
        }

    }

}

function getImage($fileName, $source) {
    $retVal = false;

    // Verify the image is okay first
    $url = $source . $fileName;
    $headers = @get_headers($url, 1);

    if ($headers && strpos($headers[0], '200') !== false && (int) $headers['Content-Length'] > 0) {
        $retVal = file_get_contents($url);
        $retVal = (object) [
            'name' => $fileName,
            'data' => $retVal,
            'contentType' => $headers['Content-Type']
        ];
    }

    return $retVal;
}

/**
 * Resizes an image for mobile optimization
 */
function resizeImage($file) {
    // Write the buffer to a file
    $tmpFile = tempnam(sys_get_temp_dir(), 'rbmobile');
    file_put_contents($tmpFile, $file->data);

    $img = null;
    switch ($file->contentType) {
        case 'image/jpeg':
        case 'image/jpg':
            $img = @imagecreatefromjpeg($tmpFile);
            break;
        case 'image/png':
            $img = @imagecreatefrompng($tmpFile);
            break;
        case 'image/gif':
            // I don't want to handle resizing animated gifs,
            // so allow them to just fall through
            break;
        default:
            // Any other data type, just 404 and bail
            send404();
            break;
    }

    // If no image was loaded, just 404
    if (!$img) {
        send404();
    }

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

        // Save the image as a JPEG utilizing our existing temp file and
        // read the new file back into the data buffer and update the MIME type
        imagejpeg($resizedImg, $tmpFile, 80);
        $file->data = file_get_contents($tmpFile);
        $file->contentType = 'image/jpg';

        // Clean up
        imagedestroy($resizedImg);
    }

    unlink($tmpFile);
    imagedestroy($img);
}

$fileName = $_GET['_q'];

// /m/ gets treated specially as this denotes a mobile friendly image
$path = explode('/', $fileName);
if (!$path[0]) {
    array_shift($path);
}
$isMobile = $path[0] === 'm';
if ($isMobile) {
    array_shift($path);
}
$fileName = '/' . implode('/', $path);

// Loop through all available sources until we get on that works
foreach ($IMAGE_SOT as $source) {
    $file = getImage($fileName, $source);
    if ($file) {
        break;
    }
}

// If anything is amiss, just 404
if (!$file) {
    send404();
} else {
    // For mobile, resize the image and cache it off
    // to the real m/ suddirectory
    if ($isMobile) {
        $fileName = '/m' . $fileName;
        resizeImage($file);
    }

    prepDirectoryPath($fileName);
    file_put_contents(LOCAL_PATH . '/' . $fileName, $file->data);
    header('Content-Type: ' . $file->contentType);
    echo $file->data;
}
