<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$queue = $argv[1] ?? 'default';

$hobbyist = new src\Hobbyist(new Predis\Client());
$hobbyist->run($queue);
