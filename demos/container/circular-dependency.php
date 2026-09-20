<?php

declare(strict_types=1);

namespace demos\container\circularDependency;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Exception;
use src\Container;

final readonly class DatabaseConnection
{
    public function __construct(
        public UserRepository $userRepository,
    ) {}
}

final readonly class UserRepository
{
    public function __construct(
        public DatabaseConnection $databaseConnection,
    ) {}
}

$container = new Container();

echo "Resolving DatabaseConnection...\n";
echo "DatabaseConnection needs UserRepository.\n";
echo "UserRepository needs DatabaseConnection.\n\n";

try {
    $container->make(DatabaseConnection::class);
} catch (Exception $exception) {
    echo $exception->getMessage() . "\n";
}
