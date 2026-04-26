<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/LogMessageHobby.php';
require_once __DIR__ . '/DelayedMessageHobby.php';
require_once __DIR__ . '/FailingHobby.php';

use demos\normal\DelayedMessageHobby;
use demos\normal\FailingHobby;
use demos\normal\LogMessageHobby;
use src\Dispatcher;

$dispatcher = new Dispatcher(new Predis\Client());

$dispatcher->dispatch(new LogMessageHobby('Hello from the normal queue demo.'));
$dispatcher->dispatch(new DelayedMessageHobby('This normal hobby was delayed.'));
$dispatcher->dispatch(new FailingHobby());

echo "Dispatched normal hobbies to queue:normal-demo\n";
