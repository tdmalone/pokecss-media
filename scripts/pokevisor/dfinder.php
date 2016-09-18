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
    'pokemon/icons-right'
];
$currentFolder      = (isset($_GET['f']) && in_array($_GET['f'], $folders)) ? $_GET['f'] : $folders[0];
$notFoundOnly       = (isset($_GET['nf']) && ($_GET['nf'] == '1'));
$removeDuplicates   = (isset($_GET['rm']) && ($_GET['rm'] == '1'));
$currentFolder      = str_replace('/', DIRECTORY_SEPARATOR, $currentFolder);
$folderPath         = $basePicPath . DIRECTORY_SEPARATOR . $currentFolder;
$folderUrl          = $basePicUrl . URL_SEPARATOR . $currentFolder;
$currentFolderClass = explode(DIRECTORY_SEPARATOR, $currentFolder); // remove parent dirs
$currentFolderClass = array_pop($currentFolderClass);

$data = include $basePath . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'get_pokemon_list.php';

###############
// HELPERS:

$handleRow = function ($name, $ext = '.gif')
use ($fallbackPicUrl, $folderPath, $folderUrl, $notFoundOnly)

{
$picFilePath   = $folderPath . DIRECTORY_SEPARATOR . $name . $ext;
$picFileExists = file_exists($picFilePath);
$picFileUrl    = $picFileExists ?
    ($folderUrl . URL_SEPARATOR . $name . $ext) : $fallbackPicUrl;

if ($picFileExists && $notFoundOnly) {
    return;
}
?>
<div class="pkv <?php echo $picFileExists ? 'pkv-pic-found' : 'pkv-pic-not-found'; ?>" title="<?= $picFilePath ?>">
    <p class="pkv-name"><?= $name ?></p>
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
    <h1>Pokevisor - Pokemon Sprites Duplicate Finder for pokeimg</h1>
    <p>
        This tool is for development purposes only and is not meant for being published on a website.
        <br>
        This tool finds the duplicated sprites in case the default form and the species name are different,
        for example: unown and unown-a as default form may share the same sprite, so it is not necessary to
        duplicate the files. This tool helps identifying those cases.
        <br>
        <a href="viewer.php">Go to Viewer tool</a>
    </p>
    <form action="dfinder.php" method="GET" class="form" style="max-width: 500px;">
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
        <div class="checkbox">
            <label>
                <input type="checkbox" name="nf" value="1"> Remove found duplicated files
            </label>
        </div>
        <button type="submit" class="btn btn-primary">View</button>
    </form>
    <div class="pkv-list pkv-list-<?= $currentFolderClass ?>">
        <h2>Folder: <?= $currentFolder ?></h2>
        <?php

        if (in_array($currentFolder, ['pokemon/icons-left', 'pokemon/icons-right'])) {
            foreach ($data as $pokemon) {
                if ($pokemon['default_form'] != $pokemon['species']) {
                    $alias     = sprintf('%04d', $pokemon['id']);
                    $formParts = explode('-', $pokemon['default_form'], 2);
                    $formAlias = $alias . '-' . array_pop($formParts);

                    $speciesFile = $folderPath . DIRECTORY_SEPARATOR . $alias . '.png';
                    $formFile    = $folderPath . DIRECTORY_SEPARATOR . $formAlias . '.png';

                    if (
                        file_exists($speciesFile)
                        && file_exists($formFile)
                        && (sha1_file($speciesFile) == sha1_file($formFile))
                    ) {
                        // Remove the related form file
                        if($removeDuplicates){
                            unlink($formFile);
                        }
                        $handleRow($alias, '.png');
                        $handleRow($pokemon['default_form']);
                    }
                }
            }
        } else {
            foreach ($data as $pokemon) {
                if ($pokemon['default_form'] != $pokemon['species']) {
                    $speciesFile = $folderPath . DIRECTORY_SEPARATOR . $pokemon['species'] . '.gif';
                    $formFile    = $folderPath . DIRECTORY_SEPARATOR . $pokemon['default_form'] . '.gif';

                    if (
                        file_exists($speciesFile)
                        && file_exists($formFile)
                        && (sha1_file($speciesFile) == sha1_file($formFile))
                    ) {
                        // Remove the related form file
                        if($removeDuplicates){
                            unlink($formFile);
                        }
                        $handleRow($pokemon['species']);
                        $handleRow($pokemon['default_form']);
                    }
                }
            }
        }
        ?>
    </div>
</div>
</body>
</html>