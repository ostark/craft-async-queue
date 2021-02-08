# AsyncQueue Changelog

All notable changes to this project will be documented in this file.

## [2.2.0] - 2021-02-05
### Changed
- Added support for `symfony/process:^5.0` 
- Updated `phpunit/phpunit` 
- Removed `version` from composer.json 
- Switched to `psalm` for static analysis

## [2.1.1] - 2020-04-03
### Fix
- Catch Exception when trying to count reserved jobs

## [2.1.0] - 2020-03-26
### Changed
- Replaced  `ProcessPool` with `Ratelimiter` to limit the number of concurrent queue runners
- Clean up: Removed unnecessary doc blocks in favour of type hints

## [2.0.0] - 2019-01-30
### Changed
- Decoupled `QueueCommand` form `QueueHandler`
- Allow custom modifications via `QueueCommand::EVENT_PREPARE_COMMAND` event
- Requires `symfony/process: ^4.2.0`

### Added
- Unit tests
- Utility to perform tests in the Craft CP
- Support for `DISABLE_ASYNC_QUEUE` env var to disable the plugin in certain environments


## [1.4.0] - 2018-12-17

### Changed
- Added `symfony/process:^4.0` as a direct dependency
- Added logging of `ProcessPool` access

## [1.3.1] - 2018-04-05
### Changed
- Changed version constraint to `craftcms/cms: ^3.0.0`

## [1.3.0] - 2018-02-22
### Changed
- Process pool implemented to restrict concurrency
- Concurrency configurable via `ASYNC_QUEUE_CONCURRENCY` ENV var (default: 2)
- Lifetime of pool configurable via `ASYNC_QUEUE_POOL_LIFETIME` ENV var (default: 3600 seconds) 

## [1.2.0] - 2018-02-19
### Changed
- Prevent multiple background processes
- No `nice` on Windows
- changed log level from `info` to `trace` 

## [1.1.5] - 2017-12-05
### Changed
- Requires Craft 3.0.0-RC1

## [1.1.4] - 2017-11-25
### Changed
- Requires Craft 3.0.0-RC1 (alias)

## [1.1.3] - 2017-11-15
### Changed
- Now we use Symfony\Component\Process\PhpExecutableFinder (thanks @phoob)


## [1.1.2] - 2017-11-06
### Added
- Craft::info() logger 2x


## [1.1.0] - 2017-10-13
### Changed
- Added AFTER_PUSH listener
- Removed custom Queue class


## 1.0.0 - 2017-08-22
### Added
- Initial release
