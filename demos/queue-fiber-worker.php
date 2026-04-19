<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/DemoAwaitResolver.php';
require_once __DIR__ . '/QueuedFiberAwaitHobby.php';
require_once __DIR__ . '/QueuedPlainLogHobby.php';

use demos\DemoAwaitResolver;
use src\CooperativeExecutor;
use src\Hobbyist;

$hobbyist = new Hobbyist(
    new Predis\Client(),
    new CooperativeExecutor(new DemoAwaitResolver()),
);

$hobbyist->run('fiber-worker-demo');
