## Changelog

### v1.0.0
- `Added` No-cache tags now require variables to be passed
- `Fixed` Fixed issue with relative/storage filepaths on some hosts – cheers @mmikkel :)
- `Fixed` Fixed conflicts with the [Minify plugin](https://github.com/nystudio107/minify)

--

#### v0.1.5
- `Fixed` No-cache blocks now function correctly in loops

#### v0.1.4
- `Improved` Improved compilation reliability
- `Fixed` Global variables now stay uncached (fixes bug in last release)

#### v0.1.3
- `Fixed` Cached contexts for `nocache` blocks now take priority over the current context

#### v0.1.2
- `Improved` Slight stability improvements when requiring files and loading cached contexts

#### v0.1.1
- `Added` Added ability to pass context to `nocache` blocks

#### v0.1.0
- Initial release
