# INILib
[![Latest Stable Version](https://poser.pugx.org/sierrakomodo/inilib/version)](https://packagist.org/packages/sierrakomodo/inilib)
[![Latest Unstable Version](https://poser.pugx.org/sierrakomodo/inilib/v/unstable)](//packagist.org/packages/sierrakomodo/inilib)
[![Total Downloads](https://poser.pugx.org/sierrakomodo/inilib/downloads)](https://packagist.org/packages/sierrakomodo/inilib)
[![License](https://poser.pugx.org/sierrakomodo/inilib/license)](https://github.com/SierraKomodo/INILib/blob/master/LICENSE)
[![StyleCI](https://styleci.io/repos/89872921/shield?branch=master)](https://styleci.io/repos/89872921)
[![Build Status](https://travis-ci.org/SierraKomodo/INILib.svg?branch=master)](https://travis-ci.org/SierraKomodo/inilib)

A PHP library to provide better handling of parsing, editing, and writing INI files

Please note this project exists primarily as a learning experience. Any and all constructive feedback is welcome and requested.

Special thanks to the following people for their help and feedback with this project:
 - [Mike Brant (Code Review Stack Exchange)](https://codereview.stackexchange.com/users/23727/mike-brant)

Version 0.1.0-review.2 - *Peer review version 2*

*Currently in development; Not fully tested yet.*

# Requirements
 - PHP 7.0 or greater
 - Composer (Technically optional. Highly recommended for installation)

# Installation
## For integration with other projects
Via composer (Recommended):
 - Execute the following composer command in your project directory: `composer require sierrakomodo/inilib` OR Modify your composer.json file to include `sierrakomodo/inilib` under the require section
 - In your PHP files, add the following use statement: `use SierraKomodo/INILib`

Manual:
 - Download the release version of your choice (Latest release is always recommended)
 - Copy the contents of the `src/` directory to a location of your choice
 - Use whatever autoloader or require/include method best fits your project
 - In your PHP files, add the following use statement: `use SierraKomodo/INILib/IniFile`
