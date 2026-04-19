<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/DemoAwaitResolver.php';
require_once __DIR__ . '/FiberAwaitDemoHobby.php';

use demos\DemoAwaitResolver;
use demos\FiberAwaitDemoHobby;
use src\CooperativeExecutor;

$executor = new CooperativeExecutor(new DemoAwaitResolver());

$executor->schedule(new FiberAwaitDemoHobby('acme'));
$executor->schedule(new FiberAwaitDemoHobby('globex'));
$executor->schedule(new FiberAwaitDemoHobby('initech'));

$executor->runUntilEmpty();
