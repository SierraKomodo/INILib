# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).


## v0.1.0-review.2 - 2017-06-06
### Added
 - Travis-CI and StyleCI checks
 - Single, combined INILib class
 - INILibException class, extending Exception, to allow catching of INILib specific exceptions with specific 'error codes'

### Changed
 - File handlers to use SplFileObject instead of fopen(), fwrite(), etc
 - Renamed INIController to INILib to better fit the class's role

### Removed
 - Separate Controller, StaticController, and ObjectController classes
 - PhpDocumentor is no longer used by require-dev


## v0.1.0-review.1 - 2017-05-08
 - Initial version.
