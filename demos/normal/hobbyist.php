<?php

declare(strict_types=1);

$container = require dirname(__DIR__, 2) . '/bootstrap/container.php';
require_once __DIR__ . '/LogMessageHobby.php';
require_once __DIR__ . '/DelayedMessageHobby.php';
require_once __DIR__ . '/FailingHobby.php';

use src\Hobbyist;

$hobbyist = $container->make(Hobbyist::class);
$hobbyist->run('normal-demo');
