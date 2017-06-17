# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).


## [Unreleased]
### Added
 - `IniFile::fetchSection()` method to retrieve full sections from memory
 - `IniFile::deleteSection()` method to delete full sections from memory
 - `IniFile::setSection()` method to modify or add full sections in memory

### Changed
 - Included installation instructions in `README.md`
 - Renamed `INILib` class to `IniFile`. Namespace remains the came - Use statements should now be `use SierraKomodo/INILib/IniFile;`
 - Renamed `INILibException` to `IniFileException`
 - Renamed various methods in `IniFile`:
    - `IniFile::dataArray()` is now `IniFile::fetchDataArray()`
    - `IniFile::setKey()` is now `IniFile::setEntry()`
    - `IniFile::deleteKey()` is now `IniFile::deleteEntry()`
    - `IniFile::saveData()` is now `IniFile::saveDataToFile()`
 - Changed default scanner mode in `IniFile::__construct()` to `INI_SCANNER_TYPED`
 - Scanner mode defined in `IniFile::__construct()` is now remembered. `IniFile::parseIniFile()` no longer accepts a scanner mode parameter

### Removed
 - `IniFileException::NO_ERR` constant
 - Unused constants removed from `IniFile` class


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
