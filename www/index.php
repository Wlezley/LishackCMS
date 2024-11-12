<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tracy\Debugger;

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' && isset($_SERVER['SERVER_PORT']) && in_array($_SERVER['SERVER_PORT'], [80, 82])) {
        $_SERVER['HTTPS'] = 'On';
        $_SERVER['SERVER_PORT'] = 443;
    } elseif ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http' && isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 80) {
        $_SERVER['HTTPS'] = 'Off';
        $_SERVER['SERVER_PORT'] = 80;
    }
}

define('VERSION', '0.1a');
define('DEFAULT_LANG', 'cz');
define('DEFAULT_PAGE', 'home');
define('ROOT_DIR', '../');
define('ROOT_DIR_ABSOLUTE', __DIR__ . '/' . ROOT_DIR);
define('APP_DIR', ROOT_DIR . 'app/');
define('FILE_CACHE_DIR', ROOT_DIR . 'temp/cache/');

$configurator = App\Bootstrap::boot();
$container = $configurator->createContainer();

if (empty($baseUrl)) {
    $baseUrl = 'http' . ($_SERVER['SERVER_PORT'] == 443 ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';
}
$baseUrl = str_replace('admin/', '', $baseUrl);
$homeUrl = str_replace('www/', '', $baseUrl);

define('BASE_URL', $baseUrl);
define('HOME_URL', $homeUrl);
define('ADMIN_HOME_URL', HOME_URL . 'admin/');
define('DEBUG', Debugger::$productionMode === Debugger::Development);
define('PROJECT_SETTINGS', $container->getParameters());

// Installer trigger
$installer = $container->getByType(\App\Models\Installer::class);
$httpRequest = $container->getByType(\Nette\Http\Request::class);
$baseUrl = $httpRequest->getUrl()->getBaseUrl();
$absUrl = $httpRequest->getUrl()->getAbsoluteUrl();

if (DEBUG && !$installer->isInstalled() && !str_starts_with($absUrl, $baseUrl . 'install/')) {
    header('Location: ' . $baseUrl . 'install/', true, 302);
    exit;
}

$application = $container->getByType(Nette\Application\Application::class);
$application->run();
