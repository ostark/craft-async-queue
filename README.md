# Async (Background) Queue

With Craft's job queue you can run heavy tasks in the background. Unfortunately, this is not entirely true, when `runQueueAutomatically => true` (default), the job queue is handled by a ajax (FPM) call.
With many jobs in the queue and limited PHP-FPM processes this can break your site.  

This plugin replaces Craft's default queue handler and moves queue execution to a non-blocking background process.
The command `craft queue/run` gets executed right after you push a Job to the queue. 

[Here](https://github.com/craftcms/cms/issues/1952) you can find the initial discussion I started at `craftcms/cms`.

## Requirements

* Craft 3
* Permissions to execute a php binary
* proc_open()

## Installation

1. Install with Composer via `composer require ostark/craft-async-queue` from your project directory
2. Install plugin with this command `php ./craft install/plugin async-queue` or in the Craft Control Panel under Settings > Plugins

## Configuration (optional)

The plugin uses [symfony/process](https://github.com/symfony/process) to execute the `php` binary. Usually the binary is located in `/usr/bin/`, but other other common locations are auto detected as well. With the ENV var `PHP_BINARY` you can explicitly set the path, e.g. in your .env file like this:
```
PHP_BINARY="/usr/local/Cellar/php71/7.1.0_11/bin/php"
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
