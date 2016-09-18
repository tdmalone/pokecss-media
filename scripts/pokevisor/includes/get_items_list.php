<?php

$basePath  = realpath(__DIR__);
$dbFile    = $basePath . DIRECTORY_SEPARATOR . 'database.sqlite';
$cacheFile = $basePath . DIRECTORY_SEPARATOR . 'items.json';

if (file_exists($cacheFile)) {
    $data = json_decode(@file_get_contents($cacheFile), true);
    if ( ! is_array($data)) {
        throw new Exception('The cache file (items.json) is corrupted.');
    }

    return $data;
}

if ( ! file_exists($dbFile)) {
    throw new \Exception('Database file not found. Please add a database file generated with metaunicorn/pokedata.');
}
$pdo = new \PDO('sqlite:' . $dbFile, null, null, [\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]);

$data  = [];
$query = $pdo->query('SELECT * FROM items ORDER BY id');

while ($row = $query->fetch()) {
    $data[] = [
        'id'   => $row['id'],
        'name' => $row['codename']
    ];
}

$data     = array_values($data);
$jsonData = json_encode($data, JSON_PRETTY_PRINT);
file_put_contents($cacheFile, $jsonData);

return $data;