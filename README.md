# Hobby

A lightweight showcase of Laravel-style job queues in plain PHP, backed by Redis.

## Requirements

- PHP >= 8.3
- Redis
- Composer

## Installation

```bash
composer install
```

## Running the Hobbyist

The hobbyist is the worker process that listens on a queue and processes hobbies as they arrive.

```bash
# listen on the default queue
php hobbyist.php

# listen on a specific queue
php hobbyist.php emails
```

## Dispatching a Hobby

```php
require_once __DIR__ . '/vendor/autoload.php';

use hobbies\LogMessageHobby;

$dispatcher = new src\Dispatcher(new Predis\Client());

// dispatches to whichever queue the hobby declares via #[OnQueue]
$dispatcher->dispatch(new LogMessageHobby('Hello from the queue!'));
```
