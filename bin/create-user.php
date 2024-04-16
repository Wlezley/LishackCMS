<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';


$container = App\Bootstrap::boot()
	->createContainer();

if (!isset($argv[2])) {
	echo '
Add new user to database.

Usage: create-user.php <name> <password>
';
	exit(1);
}

[, $username, $password] = $argv;

$manager = $container->getByType(App\Models\Authenticator::class);

// try {
	$manager->addUser($username, $password);
	echo "User $username was added.\n";

// } catch (App\Model\DuplicateNameException $e) {
// 	echo "Error: duplicate name.\n";
// 	exit(1);
// }
