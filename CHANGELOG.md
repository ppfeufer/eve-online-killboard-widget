
# Change Log

## [In Development](https://github.com/ppfeufer/eve-online-killboard-widget/tree/development)
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.14...development)
- in development

## [0.14](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.14) - 2017-09-29
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.13...v0.14)
### Changed
- Updated the code to the recent changes in field names and so on in the zKillboard API (thanks zkb dude ...)

## [0.13](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.13) - 2017-09-25
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.12...v0.13)
### Fixed
- Implemented a check if the cache might be empty, so redo it
- Additional check for CCPs strict mode, which is actually not so strict and still can give more than one result ... (CCPLEASE get your shit sorted ...)
- Positioning

## [0.12](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.12) - 2017-08-26
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.11...v0.12)
### Changed
- Set different transient cache times for different API calls
- Renamed transient cache to build a common cache for my plugins that uses the same API calls

## [0.11](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.11) - 2017-08-24
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.10...v0.11)
### Changed
- Moved cache directory to a more common place

## [0.10](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.10) - 2017-08-23
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.9...v0.10)
### Changed
- Implemented a better check if we have a kill or a loss mail

### Fixed
- Missing `$args['after_widget']` added again

## [0.9](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.9) - 2017-08-21
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.8...v0.9)
### Added
- Pilot to the list of possible entities to select from
- Possibility to have multiple widgets for different entities (Pilot/Corp/Alliance)

### Changed
- Complete rework of the API stuff. No more XML API calls. HOORAY \o/

## [0.8](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.8) - 2017-08-18
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.7.1...v0.8)
### Changed
- Creation of cache directories

## [0.7.1](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.7.1) - 2017-08-18
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.7...v0.7.1)
### Fixed
- Excessively high cache time for ESI API information reduced from 3600 hours (108.3 days) down to 2 hours.

## [0.7](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.7) - 2017-08-18
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.6...v0.7)
### Added
- Image optimization for cached images
- max-width for images

### Changed
- Switched codebase to short array syntax

### Removed
- Last fragments of am earlier change

## [0.6](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.6) - 2017-08-13
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.5...v0.6)
### Changes
- Codebase reorganized

## [0.5](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.5) - 2017-08-12
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.4...v0.5)
### Changes
- Dropped an additional check that doesn't make sense for this plugin
- Tooltips
- Switched API calls to ESI API except one that the ESI doesn't support yet (CCPLEASE!)

## [0.4](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.4) - 2017-08-12
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.3.1...v0.4)
### Changes
- Dummy Image optimized
- A lot of code optimizations

## [0.3.1](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.3.1) - 2017-08-11
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.3...v0.3.1)
### Fixed
- Usage of a wrong variable
- Ignoring NPC only killmails

## [0.3](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.3) - 2017-08-11
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.2...v0.3)
### Added
- Ship, corp and alliance images

### Changed
- Prevent direct access to our classes
- Writing empty index.php files into our cache directories, so the directory listing doesn't work there

## [0.2](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.2) - 2017-08-11
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.1...v0.2)
### Changed
- First "official" release
- Links to kill mails on zKillboard now open in a new browser tab

### Added
- This changelog :-)
- Dummy structure that will be replaced once the ajax call is made. (only in cache workaround mode)

## [v0.1](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.1) - 2017-08-10
### Changed
- First "unofficial" release, still not considered final or stable or what ever :-)

### Added
- Sidebar Widget
- Widget configuration
