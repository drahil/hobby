<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/FiberSleepDemoHobby.php';

use demos\FiberSleepDemoHobby;
use src\CooperativeExecutor;

$executor = new CooperativeExecutor();

$executor->schedule(new FiberSleepDemoHobby('alpha', 0.5));
$executor->schedule(new FiberSleepDemoHobby('bravo', 0.75));
$executor->schedule(new FiberSleepDemoHobby('charlie', 1.0));

$executor->runUntilEmpty();
