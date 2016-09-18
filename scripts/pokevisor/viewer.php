<?php

const URL_SEPARATOR = '/';

$basePath = realpath(__DIR__);
$baseUrl  = trim("http://$_SERVER[HTTP_HOST]" . dirname($_SERVER['REQUEST_URI']), '/');
$rootPath = dirname(dirname($basePath));
$rootUrl  = dirname(dirname($baseUrl));

$fallbackPicUrl = $baseUrl . '/fallback.png';
$basePicUrl     = $rootUrl . '/graphics';
$basePicPath    = $rootPath . '/graphics';

$folders            = [
    'pokemon/ani-front',
    'pokemon/ani-front-shiny',
    'pokemon/ani-back',
    'pokemon/ani-back-shiny',
    'pokemon/front',
    'pokemon/front-shiny',
    'pokemon/back',
    'pokemon/back-shiny',
    'pokemon/icons-left',
    'pokemon/icons-right',
    'items',
];
$currentFolder      = (isset($_GET['f']) && in_array($_GET['f'], $folders)) ? $_GET['f'] : $folders[0];
$notFoundOnly       = (isset($_GET['nf']) && ($_GET['nf'] == '1'));
$copyIfNotFound     = false; //(isset($_GET['cp']) && ($_GET['cp'] == '1'));
$currentFolder      = str_replace('/', DIRECTORY_SEPARATOR, $currentFolder);
$folderPath         = $basePicPath . DIRECTORY_SEPARATOR . $currentFolder;
$folderUrl          = $basePicUrl . URL_SEPARATOR . $currentFolder;
$currentFolderClass = explode(DIRECTORY_SEPARATOR, $currentFolder); // remove parent dirs
$currentFolderClass = array_pop($currentFolderClass);

if ($currentFolder == 'items') {
    $data = include $basePath . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'get_items_list.php';
} else {
    $data = include $basePath . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'get_pokemon_list.php';
}

###############
// HELPERS:

$copyFromMain = function ($name, $ext = '.gif') use ($currentFolder, $folderPath, $data) {
    if ($currentFolder == 'items') {
        return;
    }
    $picFilePath = $folderPath . DIRECTORY_SEPARATOR . $name . $ext;
    if ( ! file_exists($picFilePath)) {
        $mainName = null;
        foreach ($data as $pkm) {
            if ( ! isset($pkm['species'])) {
                break;
            }
            if ($pkm['species'] == $name) {
                $mainName = $pkm['default_form'];
            }
        }
        if ( ! $mainName) {
            return;
        }
        $mainPicFilePath = $folderPath . DIRECTORY_SEPARATOR . $mainName . $ext;
        if (file_exists($mainPicFilePath)) {
            echo $mainPicFilePath . '<br>';
            copy($mainPicFilePath, $picFilePath);
        }
    }
};

$handleRow = function ($name, $ext = '.gif', $customTitle = null)
use ($fallbackPicUrl, $folderPath, $folderUrl, $notFoundOnly, $copyIfNotFound, $copyFromMain)

{
$picFilePath   = $folderPath . DIRECTORY_SEPARATOR . $name . $ext;
$picFileExists = file_exists($picFilePath);
if ( ! $picFileExists && $copyIfNotFound) {
    $copyFromMain($name, $ext);
}
$picFileExists = file_exists($picFilePath);
$picFileUrl    = $picFileExists ?
    ($folderUrl . URL_SEPARATOR . $name . $ext) : $fallbackPicUrl;

if ($picFileExists && $notFoundOnly) {
    return;
}
?>
<div class="pkv <?php echo $picFileExists ? 'pkv-pic-found' : 'pkv-pic-not-found'; ?>" title="<?= $picFilePath ?>">
    <p class="pkv-name"><?= $customTitle ? $customTitle : $name ?></p>
    <img src="<?= $picFileUrl ?>"/>
</div>
<?php
};

###############

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pokemon 3D Sprites Visualizer</title>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main {
            padding: 0 20px;
        }

        .pkv-list {
            position: relative;
        }

        .pkv {
            position: relative;
            display: inline-block;
            width: 300px;
            height: 200px;
            margin: 0 8px 8px 0;
            text-align: center;
            border: 1px solid #ddd;
            background: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pkv img {
            position: relative;
            max-width: 100%;
            max-height: 150px;
            vertical-align: bottom;

        .pkv-name {
            position: relative;
            font-family: Monaco, sans-serif, monospace;
            font-size: 12px;
            text-align: center;
        }

        .pkv-list-icons-left .pkv,
        .pkv-list-icons-right .pkv,
        .pkv-list-items .pkv {
            width: 80px;
            height: 80px;
        }

        .pkv-list-icons-left .pkv img,
        .pkv-list-icons-right .pkv img,
        .pkv-list-items .pkv img {
            position: absolute;
            display: inline-block;
            vertical-align: bottom;
            left: 50%;
            margin-left: -20px;
            bottom: 5px;
            max-width: 40px;
            height: auto;
        }

        .pkv.pkv-pic-not-found {
            background: #ef5f43;
        }
    </style>
</head>
<body>
<div class="main">
    <h1>Pokevisor - Pokemon Sprites Viewer for pokeimg</h1>
    <p>
        This tool is for development purposes only and is not meant for being published on a website.
        <br>
        The main purpose is to quickly identify missing sprites, fixing names, etc.
        <br>
        <a href="dfinder.php">Go to Duplicate Finder tool</a>
    </p>
    <form action="viewer.php" method="GET" class="form" style="max-width: 500px;">
        <div class="form-group">
            <label>Folder: </label>
            <select name="f" class="form-control">
                <?php
                foreach ($folders as $folder):
                    ?>
                    <option <?php
                    if ($folder == $currentFolder) {
                        echo ' selected ';
                    }
                    ?> value="<?= $folder ?>"><?= $folder ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="nf" value="1"> Not found only
            </label>
        </div>
        <!--<div class="checkbox">
            <label>
                <input type="checkbox" name="cp" value="1"> Copy from default form if not found (only for Pokemon
                sprites)
            </label>
        </div>-->
        <button type="submit" class="btn btn-primary">View</button>
    </form>
    <div class="pkv-list pkv-list-<?= $currentFolderClass ?>">
        <h2>Folder: <?= $currentFolder ?></h2>
        <?php

        if ($currentFolder == 'items') {
            foreach ($data as $item) {
                $name = $item['name'];
                if(preg_match('/^data\-card.*/', $name)){
                    $handleRow('none', '.png', $item['name']);
                    continue;
                }
                if(preg_match('/^tm[\d]{1,3}$/', $name)){
                    $handleRow('tm-normal', '.png', $item['name']);
                    continue;
                }
                if(preg_match('/^hm[\d]{1,3}$/', $name)){
                    $handleRow('hm-normal', '.png', $item['name']);
                    continue;
                }
                $handleRow($item['name'], '.png');
            }
        } elseif (in_array($currentFolder, ['pokemon/icons-left', 'pokemon/icons-right'])) {
            foreach ($data as $pokemon) {
                $id    = $pokemon['id'];
                $alias = sprintf('%04d', $id);
                $handleRow($alias, '.png');
                foreach ($pokemon['forms'] as $form) {
                    $formParts = explode('-', $form, 2);
                    $formAlias = $alias . '-' . array_pop($formParts);
                    if ($form == $pokemon['default_form']) {
                        // Default forms share the same sprites as the species
                        $handleRow($alias, '.png', $formAlias);
                        continue;
                    }
                    $handleRow($formAlias, '.png');
                }
            }
        } else {
            foreach ($data as $pokemon) {
                $handleRow($pokemon['species']);
                foreach ($pokemon['forms'] as $form) {
                    if ($form == $pokemon['default_form']) {
                        // Default forms share the same sprites as the species
                        $handleRow($pokemon['species'], '.gif', $form);
                        continue;
                    }
                    $handleRow($form);
                }
            }
        }
        ?>
    </div>
</div>
</body>
</html>