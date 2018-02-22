# AsyncQueue Changelog

All notable changes to this project will be documented in this file.

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
