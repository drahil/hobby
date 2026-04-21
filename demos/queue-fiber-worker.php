<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/CompanyProfileAwaitHandler.php';
require_once __DIR__ . '/CompanyRiskScoreAwaitHandler.php';

use demos\CompanyProfileAwaitHandler;
use demos\CompanyRiskScoreAwaitHandler;
use src\CompositeAwaitResolver;
use src\CooperativeExecutor;
use src\Hobbyist;

$hobbyist = new Hobbyist(
    new Predis\Client(),
    new CooperativeExecutor(new CompositeAwaitResolver([
        new CompanyProfileAwaitHandler(),
        new CompanyRiskScoreAwaitHandler(),
    ])),
);

$hobbyist->run('fiber-worker-demo');
