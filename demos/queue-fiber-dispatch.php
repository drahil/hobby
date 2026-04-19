<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/QueuedFiberAwaitHobby.php';

use demos\QueuedFiberAwaitHobby;
use src\Dispatcher;

$dispatcher = new Dispatcher(new Predis\Client());

$dispatcher->dispatch(new QueuedFiberAwaitHobby('acme'));
$dispatcher->dispatch(new QueuedFiberAwaitHobby('globex'));
$dispatcher->dispatch(new QueuedFiberAwaitHobby('initech'));

echo "Dispatched 3 fiber-managed hobbies to queue:fiber-worker-demo\n";
