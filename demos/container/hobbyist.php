<?php

declare(strict_types=1);

$container = require dirname(__DIR__, 2) . '/bootstrap/container.php';
require_once __DIR__ . '/Mailer.php';
require_once __DIR__ . '/FakeMailer.php';
require_once __DIR__ . '/ManualWelcomeEmailHobby.php';
require_once __DIR__ . '/InjectedWelcomeEmailHobby.php';

use demos\container\FakeMailer;
use demos\container\Mailer;
use src\Hobbyist;

$container->bind(Mailer::class, FakeMailer::class);

$hobbyist = $container->make(Hobbyist::class);
$hobbyist->run('container-demo');
