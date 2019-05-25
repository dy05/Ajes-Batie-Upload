<?php

$zip = new ZipArchive();
$zip->open('photos.zip', ZipArchive::CREATE);

$dirName = 'photos';

if (!is_dir($dirName)) {
    throw new Exception('Directory ' . $dirName . ' does not exist');
}

$dirName = realpath($dirName);
if (substr($dirName, -1) != '/') {
    $dirName.= '/';
}

/*
* NOTE BY danbrown AT php DOT net: A good method of making
* portable code in this case would be usage of the PHP constant
* DIRECTORY_SEPARATOR in place of the '/' (forward slash) above.
*/

$dirStack = array($dirName);
//Find the index where the last dir starts
$cutFrom = strrpos(substr($dirName, 0, -1), '/')+1;

while (!empty($dirStack)) {
    $currentDir = array_pop($dirStack);
    $filesToAdd = array();

    $dir = dir($currentDir);
    while (false !== ($node = $dir->read())) {
        if (($node == '..') || ($node == '.')) {
            continue;
        }
        if (is_dir($currentDir . $node)) {
            array_push($dirStack, $currentDir . $node . '/');
        }
        if (is_file($currentDir . $node)) {
            $filesToAdd[] = $node;
        }
    }

    $localDir = substr($currentDir, $cutFrom);
    $zip->addEmptyDir($localDir);

    foreach ($filesToAdd as $file) {
        $zip->addFile($currentDir . $file, $localDir . $file);
    }
}

$zip->close();
if (file_exists('photos.zip')){
    header('Location:photos.zip');
}