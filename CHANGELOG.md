
# Change Log

## [In Development](https://github.com/ppfeufer/eve-online-killboard-widget/tree/development)
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.25.1...development)
### Fixed
- register_widget Aufruf made compatible for PHP 7.2 (create_function() is deprecated)

## [0.25.1](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.25.1) - 2018-10-02
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.25.0...v0.25.1)
### Fixed
- An error when final blow is a structure like a Citadel or similar, which prevented the widget from loading correctly

## [0.25.0](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.25.0) - 2018-09-30
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.24.1...v0.25.0)
### Changed
- Namespaces to match WordPress's folder structure (Plugin » Plugins)
- Implemented a reset to defaults method in Swagger class to make ESI calls a bit easier to handle
- Esi client optimized for better data handling

## [0.24.1](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.24.1) - 2018-09-26
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.24.0...v0.24.1)
### Fixed
- Reintroduced cahing on calls to zKillboard API

## [0.24.0](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.24.0) - 2018-09-26
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.23.2...v0.24.0)
### Changed
- Adapted to [changes in zKillboard API](https://www.reddit.com/r/evetech/comments/9hszlv/zkill_major_breaking_change_for_api/)

## [0.23.2](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.23.2) - 2018-09-15
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.23.1...v0.23.2)
### Changed
- The way to fetch killmails from zKillboard has been improved. This should improve overall performance as well.

## [0.23.1](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.23.1) - 2018-09-14
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.23.0...v0.23.1)
### Fixed
- Activation hook is apparently not being fired on plugin update, so we have to apply a little workaround here. Thanks WordPress for removing that hook ...

## [0.23.0](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.23.0) - 2018-09-14
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.22.5...v0.23.0)
### Added
- Highlight for solo kills in zkillboard style

### Changed
- Cache handling is now done with an own database table instead of WP transient cache. This should keep the wp_options table clean.

## [0.22.5](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.22.5) - 2018-09-10
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.22.4...v0.22.5)
### Fixed
- An issue with WordPress transient cache vs. jSon data ...

## [0.22.4](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.22.4) - 2018-09-10
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.22.3...v0.22.4)
### Fixed
- Calling the right RemoteHelper (Issue #33)

## [0.22.3](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.22.3) - 2018-09-10
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.22.2...v0.22.3)
### Fixed
- Names for transient cache
- Cache times for transient cache

### Protip
After this update make sure you go to your widget settings and save it again. The way the widget settings are handled has been changed slightly to improve performance.

## [0.22.2](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.22.2) - 2018-09-10
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.22.1...v0.22.2)
### Fixed
- Some ESI calls that went wrong

## [0.22.1](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.22.1) - 2018-08-19
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.22.0...v0.22.1)
### Changed
- Should have removed the right json_decode ...

## [0.22.0](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.22.0) - 2018-08-19
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.21...v0.22.0)
### Fixed
- A json_decode that was to much has been removed

### Changed
- URL to CCP's image server

### Removed
- Local image cache (doesn't work reliably on all machines)

## [0.21](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.21) - 2018-05-29
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.20...v0.21)
### Changed
- Changed ESI URL to the new one

## [0.20](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.20) - 2018-04-24
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.19...v0.20)
### Changed
- Adapted to zKillboard API changes

## [0.19](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.19) - 2018-01-04
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.18...v0.19)
### Changed
- Plugin dir base name detection simplified

### Fixed
- Field name for corporation_name and alliance_name in ESI endpoints. Thanks CCP for changing them again :-(

## [0.18](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.18) - 2017-10-17
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.17...v0.18)
### Fixed
- Image API end point for render fixed (Really to many end points ....)

## [0.17](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.17) - 2017-10-17
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.16...v0.17)
### Fixed
- Image API end point for ships (To many end point that do te same here ...), and this time we have the right images for T2 ships :-)

## [0.16](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.16) - 2017-10-16
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.15...v0.16)
### Fixed
- Image API end point for ships

## [0.15](https://github.com/ppfeufer/eve-online-killboard-widget/releases/tag/v0.15) - 2017-10-06
[Full Changelog](https://github.com/ppfeufer/eve-online-killboard-widget/compare/v0.14...v0.15)
### Fixed
- Pilot name for final blow is now displayed again

### Changed
- Tooltip behaviour and CSS

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
