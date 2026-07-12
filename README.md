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

## Normal Queue Demo

This demo shows regular queued hobbies with the same worker/dispatcher flow as the original project.

```bash
# terminal 1
php demos/normal/hobbyist.php

# terminal 2
php demos/normal/dispatch.php
```

## Concurrent Fiber Demo

This demo uses the same two-terminal queue flow, but the hobbies are marked with `#[ExecuteConcurrently]`.

```bash
# terminal 1
php demos/concurrent/hobbyist.php

# terminal 2
php demos/concurrent/dispatch.php
```

The concurrent demo uses a demo-owned fake API awaitable to show a hobby suspending while the worker continues advancing other work.

## Sequential API Demo

This demo dispatches the same 10 company lookup hobbies, but each hobby performs blocking fake API calls.

```bash
# terminal 1
php demos/sequential-api/hobbyist.php

# terminal 2
php demos/sequential-api/dispatch.php
```

Compare it with the concurrent demo. The sequential worker processes one hobby through both fake API waits before moving to the next hobby, while the concurrent worker suspends waiting hobbies and keeps starting other ones.

## Container Injection Demo

This demo shows the before and after of a hobby that needs a mailer service.

```bash
# terminal 1
php demos/container/hobbyist.php

# terminal 2
php demos/container/dispatch.php
```

`ManualWelcomeEmailHobby` creates `FakeMailer` inside `handle()`. `InjectedWelcomeEmailHobby` keeps only serialized queue data in its constructor and receives a `Mailer` interface through `handle(Mailer $mailer)`.

The container demo worker binds that interface to the fake implementation:

```php
$container->bind(Mailer::class, FakeMailer::class);
```
