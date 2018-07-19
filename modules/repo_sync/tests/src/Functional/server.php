<?php

define('HEADER_PREFIX', 'HTTP_');

$headers = [];

foreach ($_SERVER as $name => $value) {
  if (strpos($name, HEADER_PREFIX) !== 0) {
    continue;
  }

  $name = strtr(ucwords(strtr(substr(strtolower($name), strlen(HEADER_PREFIX)), '_', ' ')), ' ', '-');
  $headers[$name] = $value;
}

file_put_contents('php://stderr', json_encode([
  'method' => $_SERVER['REQUEST_METHOD'],
  'uri' => $_SERVER['REQUEST_URI'],
  'protocol' => $_SERVER['SERVER_PROTOCOL'],
  'headers' => $headers,
  'body' => file_get_contents('php://input'),
]) . PHP_EOL);
