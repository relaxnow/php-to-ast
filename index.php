<?php
require "vendor/autoload.php";

use PhpParser\ParserFactory;

$ALLOWED_EXTENSIONS = [
    "php",
    "module",
    "inc",
    "html",
    "htm",
    "profile",
    "install",
    "engine",
    "theme",
    "php4",
    "php5",
    "php7",
    "phtml",
];

if (!isset($argv[1])) {
    echo("Please provide directory to scan" . PHP_EOL);
    exit(1);
}

$directory = $argv[1];

if (!file_exists($directory)) {
    echo("Directory does not exist '$directory'" . PHP_EOL);
    exit(1);
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

$files = [];
/** @var DirectoryIterator $file */
foreach ($rii as $file) {

    if ($file->isDir()){
        continue;
    }

    if (!in_array($file->getExtension(), $ALLOWED_EXTENSIONS)) {
        continue;
    }

    echo "Processing " . $file->getRealPath() . PHP_EOL;

    $code = file_get_contents($file);

    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

    try {
        $stmts = $parser->parse($code);

        $jsonFilePath = $file->getRealPath();
        $jsonFilePath = substr($jsonFilePath, 0, strlen($jsonFilePath) - strlen($file->getExtension())) . '.json';
        file_put_contents($jsonFilePath, json_encode($stmts, JSON_PRETTY_PRINT) . "\n");
    } catch (PhpParser\Error $e) {
        $errorFilePath = $file->getRealPath();
        $errorFilePath = substr($jsonFilePath, 0, strlen($jsonFilePath) - strlen($file->getExtension())) . '.error';
        file_put_contents($errorFilePath, $e->getMessage());
    }
}
