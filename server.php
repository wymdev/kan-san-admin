<?php

/**
 * Router for PHP's built-in web server (`composer serve`, `php artisan serve`).
 *
 * Laravel ships its own copy of this file, but that one resolves the public
 * directory from getcwd(), so it only works when the server process was started
 * from inside public/. Resolving from __DIR__ instead makes it independent of
 * the working directory. ServeCommand prefers this file when it exists, so both
 * entry points behave the same.
 */
$publicPath = __DIR__.'/public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Let the built-in server serve existing static files (assets, build output).
if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
