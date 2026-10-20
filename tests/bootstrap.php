<?php
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $baseDir = dirname(__DIR__) . '/app/';
    $relativeClass = substr($class, strlen($prefix));
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);
    $directory = strtolower(implode('/', $parts));
    $file = $baseDir . ($directory !== '' ? $directory . '/' : '') . $className . '.php';

    if (is_file($file)) {
        require $file;
    }
});
