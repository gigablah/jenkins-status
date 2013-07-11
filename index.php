<?php

error_reporting(0);

$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
$protocol = $protocol === 'HTTP/1.0' ? 'HTTP/1.0' : 'HTTP/1.1';

$http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

$uri = explode('?', $_SERVER['REQUEST_URI']);
$uri = reset($uri);

$pattern = '#^/job/([^/]+)/badge/icon$#';

if (false === preg_match($pattern, $uri, $matches)) {
    header(sprintf('%s 204 No Content', $protocol));
    exit();
}

$jobName = $matches[1];

if (false === $json = @file_get_contents('http://localhost:8080/api/json')) {
    header(sprintf('%s 204 No Content', $protocol));
    exit();
}

if (null === $status = json_decode($json, true)) {
    header(sprintf('%s 204 No Content', $protocol));
    exit();
}

if (!is_array($status) || !isset($status['jobs'])) {
    header(sprintf('%s 204 No Content', $protocol));
    exit();
}

$color = 'red';

foreach ($status['jobs'] as $job) {
    if (isset($job['name']) && isset($job['color']) && $job['name'] === $jobName) {
        $color = $job['color'];
        break;
    }
}

if ($color !== 'blue') {
    $color = 'red';
}

$status = '';

switch ($color) {
    case 'blue':
        $status = 'success';
        break;
    case 'red':
    default:
        $status = 'failure';
        break;
}

$filename = sprintf('%s://%s%s/images/%s.png', $http, $host, $uri, $status);

header('Location: ' . $filename);
exit();
