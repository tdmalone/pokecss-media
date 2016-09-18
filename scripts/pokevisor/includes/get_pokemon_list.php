<?php

$basePath  = realpath(__DIR__);
$dbFile    = $basePath . DIRECTORY_SEPARATOR . 'database.sqlite';
$cacheFile = $basePath . DIRECTORY_SEPARATOR . 'pokemon.json';

if (file_exists($cacheFile)) {
    $data = json_decode(@file_get_contents($cacheFile), true);
    if ( ! is_array($data)) {
        throw new Exception('The cache file (pokemon.json) is corrupted.');
    }

    return $data;
}

if ( ! file_exists($dbFile)) {
    throw new \Exception('Database file not found. Please add a database file generated with metaunicorn/pokedata.');
}
$pdo = new \PDO('sqlite:' . $dbFile, null, null, [\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]);

$data  = [];
$query = $pdo->query('SELECT * FROM pokemon_species ORDER BY id');

while ($row = $query->fetch()) {
    $data[$row['id']] = [
        'id'           => $row['id'],
        'species'      => $row['codename'],
        'default_form' => null,
        'forms'        => []
    ];
}

foreach ($data as $id => $d) {
    $query       = $pdo->query('SELECT * FROM pokemon WHERE species_id=' . $id . ' ORDER BY `order`, id');
    $speciesId   = $id;
    $speciesName = $d['species'];
    while ($row = $query->fetch()) {
        $pokemonId = $row['id'];
        if (($row['codename'] != $d['species']) && ! in_array($row['codename'], $data[$id]['forms'])) {
            $data[$id]['forms'][] = $row['codename'];
        }
        if (
            ($row['is_default'] == 1)
            && ($row['id'] == $speciesId)
            //&& ($row['codename'] != $d['species'])
        ) {
            // Is the default for the pokemon_species?
            $data[$id]['default_form'] = $row['codename'];
        }
        $subquery = $pdo->query('SELECT * FROM pokemon_forms WHERE pokemon_id=' . $row['id'] . ' ORDER BY `order`, id');
        while ($subrow = $subquery->fetch()) {
            if (($subrow['codename'] != $d['species']) && ! in_array($subrow['codename'], $data[$id]['forms'])) {
                $data[$id]['forms'][] = $subrow['codename'];
            }
            if (
                ($subrow['is_default'] == 1)
                && ($subrow['id'] == $speciesId)
                //&& ($subrow['codename'] != $d['species'])
            ) {
                // Is the default for the pokemon?
                $data[$id]['default_form'] = $subrow['codename'];
            }
        }
    }
}

$data     = array_values($data);
$jsonData = json_encode($data, JSON_PRETTY_PRINT);
file_put_contents($cacheFile, $jsonData);

return $data;