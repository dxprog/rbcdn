<?php

require('config.php');

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

function getImage($fileName) {
    $retVal = false;

    // Verify the image is OK first
    $url = IMAGE_SOT . $fileName;
    $headers = get_headers($url);

    if ($headers && strpos($headers[0], '200') !== false) {
        $retVal = file_get_contents($url);
    }

    return $retVal;
}

$fileName = $_GET['_q'];
$data = getImage($fileName);

// If anything is amiss, just 404
if (!$data) {
    header('HTTP/1.1 404 Not Found');
} else {
    prepDirectoryPath($fileName);
    file_put_contents(LOCAL_PATH . '/' . $fileName, $data);

    // Redirect to the file (which is to say, the same URL that was requested)
    header('Location: ' . $_SERVER['REQUEST_URI']);
}