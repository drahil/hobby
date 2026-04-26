<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/CompanyLookupHobby.php';

use demos\concurrent\CompanyLookupHobby;
use src\Dispatcher;

$dispatcher = new Dispatcher(new Predis\Client());

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
