<?php

declare(strict_types=1);

$container = require dirname(__DIR__, 2) . '/bootstrap/container.php';
require_once __DIR__ . '/LogMessageHobby.php';
require_once __DIR__ . '/DelayedMessageHobby.php';
require_once __DIR__ . '/FailingHobby.php';

use demos\normal\DelayedMessageHobby;
use demos\normal\FailingHobby;
use demos\normal\LogMessageHobby;
use src\Dispatcher;

$dispatcher = $container->make(Dispatcher::class);

$dispatcher->dispatch(new LogMessageHobby('Hello from the normal queue demo.'));
$dispatcher->dispatch(new DelayedMessageHobby('This normal hobby was delayed.'));
$dispatcher->dispatch(new FailingHobby());

echo "Dispatched normal hobbies to queue:normal-demo\n";
