<?php

header("Content-Type: application/json");

$file = __DIR__ . "/../cache/news.json";

if (!file_exists($file)) {

    echo "[]";

    exit;

}

echo file_get_contents($file);