<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use hobbies\DelayedMessageHobby;
use hobbies\FailingHobby;
use hobbies\LogMessageHobby;

$dispatcher = new src\Dispatcher(new Predis\Client());

$dispatcher->dispatch(new LogMessageHobby('Hello from the queue!'));
$dispatcher->dispatch(new FailingHobby());
$dispatcher->dispatch(new DelayedMessageHobby('This message will be logged after a delay.'));
