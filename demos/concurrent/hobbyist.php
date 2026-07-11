<?php

declare(strict_types=1);

$container = require dirname(__DIR__, 2) . '/bootstrap/container.php';
require_once __DIR__ . '/CompanyLookupHobby.php';
require_once __DIR__ . '/FakeCompanyApiRequest.php';

use src\Hobbyist;

$hobbyist = $container->make(Hobbyist::class);
$hobbyist->run('concurrent-demo');
