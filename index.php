<?php

require('config.php');

function prepDirectoryPath($file) {

    //ensure that the file is not empty
    if($file!=='' &&!empty($file) && isset($empty))
    {
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
                    if (!is_dir(LOCAL_PATH . '/' . $fullPath))
                    {
                        mkdir(LOCAL_PATH . '/' . $fullPath);
                    }
                }
            }

        }
    }

    else
    {
        return false;
    }


}

function getImage($fileName, $source) {
    $retVal = false;

    //verify that the fields are not empty
    if($fileName!=='' && !empty($fileName) && isset($fileName) && $source!=='' && !empty($source) && isset($source))
    {
        try
        {
            // Verify the image is okay first
            $url = $source . $fileName;
            $headers = get_headers($url, 1);

            if ($headers && strpos($headers[0], '200') !== false) {
                $retVal = file_get_contents($url);
                $retVal = (object) [
                    'data' => $retVal,
                    'contentType' => $headers['Content-Type']
                ];
            }
        }

        /*
         * return false if an exception occurs. may change to return
         * the exception if desired
         */
        catch(Exception $e)
        {
            return false;
        }
    }

    return $retVal;
}

//we want to verify that the value was actually passed
if(isset($_GET['q']) && trim($_GET['q'])!=='')
{
    //might need a rule here of what is allowable in the $_GET['q']
    $fileName = $_GET['_q'];
    
    // Loop through all available sources until we get on that works
    foreach ($IMAGE_SOT as $source)
    {
        $file = getImage($fileName, $source);
        if ($file) {
            break;
        }
    }

    // If anything is amiss, just 404
    if (!$file) {
        header('HTTP/1.1 404 Not Found');
    } else {
        prepDirectoryPath($fileName);
        file_put_contents(LOCAL_PATH . '/' . $fileName, $file->data);
        header('Content-Type: ' . $file->contentType);
        echo $file->data;
    }
}

//not corrected called so just a 404
else
{
    header('HTTP/1.1 404 Not Found');
}





