# Changelog

## 2.0.6 - 2019-11-16

### Changed
- No-Cache now requires Craft 3.1.24 or later

### Fixed
- Fixed issue where No-Cache blocks would disappear if compiled templates were cleared but template caches were not
- Cleanup of event listener code

## 2.0.5 - 2019-10-08

### Fixed
- Changed how No-Cache compiled template class names are set, in a way that should be more future-proof
- Replaced usage of Twig class names deprecated as of Twig 2.7

## 2.0.4 - 2019-06-07

### Fixed
- Fixed incompatibility with Craft 3.1.29

## 2.0.3 - 2019-04-24

### Fixed
- Fixed incompatibility with Craft 3.1.24

## 2.0.2 - 2019-04-04

### Fixed
- Fixed error caused by No-Cache when attempting a project config sync from a terminal (thanks @Coysh)

## 2.0.1 - 2019-03-21

### Fixed
- Fixed error with Craft 3.1.18 and later

## 2.0.0 - 2019-03-03
- Initial release for Craft 3
