<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/CompanyLookupHobby.php';
require_once __DIR__ . '/FakeBlockingCompanyApi.php';

use src\Hobbyist;

$hobbyist = new Hobbyist(new Predis\Client());
$hobbyist->run('sequential-api-demo');
