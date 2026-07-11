<?php

declare(strict_types=1);

$container = require dirname(__DIR__, 2) . '/bootstrap/container.php';
require_once __DIR__ . '/CompanyLookupHobby.php';

use demos\concurrent\CompanyLookupHobby;
use src\Dispatcher;

$dispatcher = $container->make(Dispatcher::class);

$companies = [
    'acme',
    'globex',
    'initech',
    'umbrella',
    'stark',
    'wayne',
    'wonka',
    'hooli',
    'soylent',
    'vandelay',
];

foreach ($companies as $company) {
    $dispatcher->dispatch(new CompanyLookupHobby($company));
}

echo "Dispatched 10 concurrent hobbies to queue:concurrent-demo\n";
