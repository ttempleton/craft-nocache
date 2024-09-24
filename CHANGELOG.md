# Changelog

## Unreleased

### Fixed
- Fixed an error that occurred on Craft 5 when saving entries that use CKEditor field values in their title format, where any nested entries in the CKEditor field have partial templates that use No-Cache tags (thanks @thomascoppein)

## 3.0.3 - 2024-09-14

### Fixed
- Fixed an error that occurred when using Twig 3.13 or later (thanks @jaspertandy)

## 3.0.2 - 2024-01-25

### Added
- Added Craft 5 compatibility

## 3.0.1 - 2023-11-17

### Fixed
- Fixed a bug where No-Cache blocks weren't being rendered if the `token` request query parameter was set to an invalid token

## 3.0.0 - 2022-05-05

### Added
- Added Craft 4 compatibility

### Removed
- Removed Craft 3 compatibility

## 2.0.8 - 2021-08-08

### Fixed
- Fixed an issue where Twig macros couldn't be imported into a No-Cache block from the template the No-Cache block is a part of

## 2.0.7 - 2020-12-31

### Fixed
- Fixed an issue where No-Cache tags could be used in control panel templates, though the inner content of the tags wouldn't load; now, an attempt to use the tags in the control panel will cause an exception to be thrown
- Fixed some formatting issues with No-Cache-related compiled template code

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
