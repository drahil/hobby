<?php

declare(strict_types=1);

$container = require dirname(__DIR__, 2) . '/bootstrap/container.php';
require_once __DIR__ . '/ManualWelcomeEmailHobby.php';
require_once __DIR__ . '/InjectedWelcomeEmailHobby.php';

use demos\container\InjectedWelcomeEmailHobby;
use demos\container\ManualWelcomeEmailHobby;
use src\Dispatcher;

$dispatcher = $container->make(Dispatcher::class);

$dispatcher->dispatch(new ManualWelcomeEmailHobby('manual@example.com'));
$dispatcher->dispatch(new InjectedWelcomeEmailHobby('injected@example.com'));

echo "Dispatched manual and injected hobbies to queue:container-demo\n";
