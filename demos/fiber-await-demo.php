<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/CompanyProfileAwaitHandler.php';
require_once __DIR__ . '/CompanyRiskScoreAwaitHandler.php';

use demos\CompanyProfileAwaitHandler;
use demos\CompanyRiskScoreAwaitHandler;
use hobbies\FiberAwaitDemoHobby;
use src\CompositeAwaitResolver;
use src\CooperativeExecutor;

$executor = new CooperativeExecutor(new CompositeAwaitResolver([
    new CompanyProfileAwaitHandler(),
    new CompanyRiskScoreAwaitHandler(),
]));

$executor->schedule(new FiberAwaitDemoHobby('acme'));
$executor->schedule(new FiberAwaitDemoHobby('globex'));
$executor->schedule(new FiberAwaitDemoHobby('initech'));

$executor->runUntilEmpty();
