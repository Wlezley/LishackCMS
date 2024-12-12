<?php

declare(strict_types=1);

// Source: https://gist.github.com/dg/734bebf55cf28ad6a5de1156d3099bff

// Updates all the version constraints of dependencies in the composer.json file to their latest version.
//
// usage: composer-frontline.php                (updates all Nette packages)
//        composer-frontline.php  doctrine/*    (updates all Doctrine packages)
//        composer-frontline.php  *             (updates all packages)

$composer = json_decode(file_get_contents('composer.json'));
if (isset($composer->{'minimum-stability'}) && $composer->{'minimum-stability'} !== 'stable') {
    echo "Please change 'minimum-stability' to 'stable in order to continue.\n";
    exit;
}

exec('composer outdated -D --format=json', $output, $error);
if ($error) {
    exit(1);
}

$masks = array_slice($argv, 1) ?: ['nette/*', 'tracy/*', 'latte/*'];
$outdated = json_decode(implode($output));
$upgrade = [];
foreach ($outdated->installed as $package) {
    foreach ($masks as $mask) {
        if (fnmatch($mask, $package->name)) {
            $mode = isset($composer->require->{$package->name}) ? '' : '--dev';
            $upgrade[$mode][] = $package->name;
            continue 2;
        }
    }
}

if (!$upgrade) {
    echo "nothing to update\n";
    exit;
}

foreach ($upgrade as $mode => $packages) {
    passthru('composer --no-update --no-scripts require ' . $mode . ' ' . implode(' ', $packages));
}
