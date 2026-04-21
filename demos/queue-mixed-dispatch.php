<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use hobbies\QueuedFiberAwaitHobby;
use hobbies\QueuedPlainLogHobby;
use src\Dispatcher;

$dispatcher = new Dispatcher(new Predis\Client());

$dispatcher->dispatch(new QueuedFiberAwaitHobby('acme'));
$dispatcher->dispatch(new QueuedPlainLogHobby('plain-check'));
$dispatcher->dispatch(new QueuedFiberAwaitHobby('globex'));

echo "Dispatched 2 fiber-managed hobbies and 1 plain hobby to queue:fiber-worker-demo\n";
