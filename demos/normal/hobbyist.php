<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/LogMessageHobby.php';
require_once __DIR__ . '/DelayedMessageHobby.php';
require_once __DIR__ . '/FailingHobby.php';

use src\Hobbyist;

$hobbyist = new Hobbyist(new Predis\Client());
$hobbyist->run('normal-demo');
