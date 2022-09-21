<?php

// Require Composer dependencies
require 'vendor/autoload.php';

// Setup Glide server
$server = League\Glide\ServerFactory::create([
    'source' => 'path/to/source/folder',
    'cache'  => 'path/to/cache/folder',
]);

// Fetch the image path from the URI, example: /img/users/1.jpg?w=300&h=300
$path  = substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), 5);

// Fetch and filter URI query parameters
$query = filter_input_array(INPUT_GET);

// Handle the image manipulations and output the image
$server->outputImage($path, $query);
