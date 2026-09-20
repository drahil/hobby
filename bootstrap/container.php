<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Predis\Client;
use src\Container;

$container = new Container();

$container->instance(Container::class, $container);
$container->singleton(Client::class, static fn (Container $container): Client => new Client());

return $container;
