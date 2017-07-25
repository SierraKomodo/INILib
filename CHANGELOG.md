# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

The contents of this changelog are focused on changes to overall behaviour of this library, as relevant to a developer using its classes. Internal changes that don't affect this behaviour are ommitted, or simply noted as 'Minor internal changes', 'Minor performance optimizations', etc.

## [UNRELEASED]
### Changed
 - Package is now reliant on `SierraKomodo/PhpCommonLibrary`
 

## v1.0.1 - 2017-07-12
### Changed
 - Key validation now includes keywords that cannot be used as INI keys
 - Key validation now includes additional symbols that cannot be used in INI keys


## v1.0.0 - 2017-07-06
### Changed
 - Some minor internal changes


## v0.1.1-review.3 - 2017-07-02
### Fixed
 - `IniFile::deleteEntry()` and `IniFile::deleteSection()` will now delete entries, even if `!empty()` and `isset()` checks would return `FALSE` (I.e., a value was `NULL`)
 - `IniFile::fetchEntry()` and `IniFile::fetchSection()` will now return values instead of `NULL` if the value is `0`, an empty string, or some other value that `empty()` would have returned `TRUE` on.


## v0.1.0-review.3 - 2017-07-02
### Added
 - `IniFile::fetchSection()` method to retrieve full sections from memory
 - `IniFile::deleteSection()` method to delete full sections from memory
 - `IniFile::setSection()` method to modify or add full sections in memory
 - `IniFile::__construct()` now has a boolean 'Read Only' flag as its second parameter. If set to `TRUE`, any attempts to use methods that modify the data array or save data to the file will throw a `IniFileException` with code `IniFileException::ERR_READ_ONLY_MODE`

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
 - `composer.json` `require` flag for PHP now uses `^7.0` instead of `>=7.0.0`
 - `IniFileException::__construct()` parameter 3 now requires type `\Exception` instead of `\Throwable`
 - `IniFile::parseIniData()` is now a protected method
 - `IniFile::generateFileContent()` is now a protected method
 - `IniFile::__construct()` now checks if the INI file is readable, and throws `IniFileException` with code `IniFileException::ERR_FILE_NOT_READABLE` if it's not
 - `IniFile::__construct()` now checks if the INI file exists, and throws `IniFileException` with code `IniFileException::ERR_FILE_NOT_EXIST` if it does not
 - `IniFile::__construct()` first parameter now uses `string` instead of `SplFileObject` - The file object is created within the constructor using a filepath provided

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
