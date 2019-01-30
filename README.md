# Async (Background) Queue

[![Latest Stable Version](https://poser.pugx.org/ostark/craft-async-queue/v/stable)](https://packagist.org/packages/ostark/craft-async-queue)
[![Total Downloads](https://poser.pugx.org/ostark/craft-async-queue/downloads)](https://packagist.org/packages/ostark/craft-async-queue)
[![Monthly Downloads](https://poser.pugx.org/ostark/craft-async-queue/d/monthly)](https://packagist.org/packages/ostark/craft-async-queue)



With Craft's job queue you can run heavy tasks in the background. Unfortunately, this is not entirely true, when `runQueueAutomatically => true` (default), the job queue is handled by a ajax (FPM) call.
With many jobs in the queue and limited PHP-FPM processes this can break your site.  

This plugin replaces Craft's default queue handler and moves queue execution to a non-blocking background process.
The command `craft queue/run` gets executed right after you push a Job to the queue. 

[Here](https://github.com/craftcms/cms/issues/1952) you can find the initial discussion I started at `craftcms/cms`.

## Sponsor

Development happens in my free time, but also during working hours. Thanks [fortrabbit.com](https://www.fortrabbit.com/craft-hosting)!

## Requirements

* Craft 3
* Permissions to execute a php binary
* proc_open()
* **PHP ^7.1** (for PHP 7.0 use `ostark/craft-async-queue:1.3.*`)

## Installation

```shell
cd your/craft-project
composer require ostark/craft-async-queue
php craft install/plugin async-queue
```

If you run into Composer version conflicts:
```
composer config platform --unset
composer update
php craft migrate/all
composer require ostark/craft-async-queue
php craft install/plugin async-queue
```


## Configuration (optional)

The plugin uses [symfony/process](https://github.com/symfony/process) to execute the `php` binary. Usually the binary is located in `/usr/bin/`, but other common locations are auto detected as well. With the ENV var `PHP_BINARY` you can explicitly set the path, e.g. in your .env file like this:
```
PHP_BINARY="/usr/local/Cellar/php71/7.1.0_11/bin/php"
```


By default `2` background processes handle the queue. With the `ASYNC_QUEUE_CONCURRENCY` ENV var you can modify this behaviour.
```
# No concurrency
ASYNC_QUEUE_CONCURRENCY=1

# Or max 5 background processes
ASYNC_QUEUE_CONCURRENCY=5
```

To disable the plugin in certain environments, like on Windows which is not supported yet, set the `DISABLE_ASYNC_QUEUE` ENV var.
```
DISABLE_ASYNC_QUEUE=1
```

## Tests

Beside the test suite you can run from the command line with this shortcut: `composer tests`, you can perform a test in the Craft CP.
Navigate to `Utilities` > `Async Queue Test` and hit the `Run test` button. 


## Events

The command that runs in the background is basically `php craft queue/run`, however we add some linux specific syntax that executes the command in a non-blocking way.
By setting `useDefaultDecoration` to `false` you prevent this. You have also the ability to modify the command itself. 

```
// Add handler
\yii\base\Event::on(
     \ostark\AsyncQueue\QueueCommand::class,
     \ostark\AsyncQueue\QueueCommand::EVENT_PREPARE_COMMAND,
     function(\ostark\AsyncQueue\Events\QueueCommandEvent $event) {
         $event->useDefaultDecoration = false;
         $event->commandLine = "BEFORE {$event->commandLine} AFTER";
     }
);
```




## Under the hood: Process list

**Empty queue** (only php-fpm master is running)
```
$ ps auxf | grep php

root      2953  0.0  0.0 399552 13520 ?        Ss   12:27   0:00 php-fpm: master process (/etc/php/fpm.conf)
````

**New job pushed** (php-fpm master + child + /usr/bin/php daemon started)
```
$ ps auxf | grep php

root      2953  0.0  0.0 399552 13520 ?        Ss   12:27   0:00 php-fpm: master process (/etc/php/fpm.conf)
app       3031  2.2  0.2 718520 45992 ?        S    12:31   0:00  \_ php-fpm: pool www
app       3033  1.2  0.2 280936 32808 ?        S    12:31   0:00 /usr/bin/php craft queue/run
app       3034  0.0  0.0   4460   784 ?        S    12:31   0:00  \_ sh -c /usr/bin/php craft queue/exec "1234" "0" "1"
app       3035  1.2  0.2 280928 32280 ?        S    12:31   0:00      \_ /usr/bin/php craft queue/exec 1234 0 1
```
