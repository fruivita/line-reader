# File Reader for Laravel applications

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fruivita/line-reader?logo=packagist)](https://packagist.org/packages/fruivita/line-reader)
[![GitHub Release Date](https://img.shields.io/github/release-date/fruivita/line-reader?logo=github)](/../../releases)
[![GitHub last commit (branch)](https://img.shields.io/github/last-commit/fruivita/line-reader/main?logo=github)](/../../commits/main)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/fruivita/line-reader/Unit%20and%20Feature%20tests/main?label=tests&logo=github)](/../../actions/workflows/tests.yml?query=branch%3Amain)
[![Test Coverage](https://api.codeclimate.com/v1/badges/381e769910d3740dccfe/test_coverage)](https://codeclimate.com/github/fruivita/line-reader/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/381e769910d3740dccfe/maintainability)](https://codeclimate.com/github/fruivita/line-reader/maintainability)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/fruivita/line-reader/Static%20Analysis/main?label=code%20style&logo=github)](/../../actions/workflows/static.yml?query=branch%3Amain)
[![GitHub issues](https://img.shields.io/github/issues/fruivita/line-reader?logo=github)](/../../issues)
![GitHub repo size](https://img.shields.io/github/repo-size/fruivita/line-reader?logo=github)
[![Packagist Total Downloads](https://img.shields.io/packagist/dt/fruivita/line-reader?logo=packagist)](https://packagist.org/packages/fruivita/line-reader)
[![GitHub](https://img.shields.io/github/license/fruivita/line-reader?logo=github)](../LICENSE.md)

Read large files, line by line, without causing memory overflow for **[Laravel](https://laravel.com/docs)** applications.

This package, for **[Laravel](https://laravel.com/docs)** applications, allows you to read the contents of huge files without killing your server, that is, without having to load all the contents at once in memory causing an ***out-of-memory errors***.

The strategy used here, thanks to php's **[SplFileObject](https://www.php.net/manual/en/class.splfileobject.php)**, is to read the contents of the file line by line optimizing the use of server resources and, most importantly, in an efficient way.

It is also possible to paginate the contents of the file, again, without having to load it entirely into memory thanks to php's **[LimitIterator](https://www.php.net/manual/en/class.limititerator.php)**.

```php
use FruiVita\LineReader\Facades\LineReader;

$generator = LineReader::readLines($file_path);

// or

$length_aware_paginator = LineReader::readPaginatedLines($file_path, $per_page, $page);
```

&nbsp;

---

## Table of Contents

1. [Notes](#notes)

2. [Prerequisites](#prerequisites)

3. [Installation](#installation)

4. [How it works](#how-it-works)

5. [Testing and Continuous Integration](#testing-and-continuous-integration)

6. [Changelog](#changelog)

7. [Contributing](#contributing)

8. [Code of conduct](#code-of-conduct)

9. [Security Vulnerabilities](#security-vulnerabilities)

10. [Support and Updates](#support-and-updates)

11. [Roadmap](#roadmap)

12. [Credits](#credits)

13. [Thanks](#thanks)

14. [License](#license)

---

## Notes

⭐ Internally, this package reads the file contents using php's **[SplFileObject](https://www.php.net/manual/en/class.splfileobject.php)** class. In the specific case of pagination, the **[LimitIterator](https://www.php.net/manual/en/class.limititerator.php)** is used to delimit the beginning and end of the content to be read.

❤️ Heavily inspired by the [bcremer/LineReader](https://github.com/bcremer/LineReader) package.

⬆️ [Back](#table-of-contents)

&nbsp;

## Prerequisites

1. PHP dependencies

    PHP ^8.0

    [Extensions](https://getcomposer.org/doc/03-cli.md#check-platform-reqs)

    ```bash
    composer check-platform-reqs
    ```

2. [GitHub Package Dependencies](/../../network/dependencies)

⬆️ [Back](#table-of-contents)

&nbsp;

## Installation

1. Install via **[composer](https://getcomposer.org/)**:

    ```bash
    composer require fruivita/line-reader
    ```

2. Optionally publish the translations

    ```bash
    php artisan vendor:publish --provider='FruiVita\LineReader\LineReaderServiceProvider' --tag='lang'
    ```

    The strings available for translation are as follows. Change them as needed.

    ```json
    {
        "The file entered could not be read": "The file entered could not be read"
    }
    ```

    >This package already has translations for **en** and **pt-br**.

⬆️ [Back](#table-of-contents)

&nbsp;

## How it works

1. Reading a file line by line.

    ```php
    use FruiVita\LineReader\Facades\LineReader;

    public function example()
    {
        foreach (LineReader::readLines($file_path) as $line_number => $line_content)
        {
            // $line_number is 1 when reading the 1st line, 2 when reading the 2nd line, and so on.
            // $line_content string with the contents of the line.
        }
    }
    ```

    &nbsp;

    LineReader exposes the following method to read the file line by line:

    ✏️ **readLines**

    ```php
    use FruiVita\LineReader\Facades\LineReader;

    /**
     * @param string $file_path full path of the file to be read
     * 
     * @throws \FruiVita\LineReader\Exceptions\FileNotReadableException
     *
     * @return \Generator
     */
    LineReader::readLines(string $file_path);
    ```

    &nbsp;

    🚨 **Exceptions**:

    - **readLines** throws **\FruiVita\LineReader\Exceptions\FileNotReadableException** if don't have read permission on the file or it can't be found

    &nbsp;

2. Reading the file by page.

    ```php
    use FruiVita\LineReader\Facades\LineReader;

    public function example()
    {
        $per_page = 15;
        $page = 2;

        $length_aware_paginator = LineReader::readPaginatedLines(string $file_path, int $per_page, int $page);
        
        // The index of the items in the collection respects their position in the file, that is, in the example
        // above the 1st item on page 2 will have index 16, since it is the 16th line of the file and the last
        // item on page 2 will have index 30, since it is the 30th line of the file.
    }
    ```

    &nbsp;

    LineReader exposes the following method to read the file by page:

    ✏️ **readPaginatedLines**

    ```php
    use FruiVita\LineReader\Facades\LineReader;

    /**
     * @param string $file_path full path of the file to be read
     * @param int    $per_page
     * @param int    $page
     * @param string $page_name
     *
     * @throws \FruiVita\LineReader\Exceptions\FileNotReadableException
     * @throws \InvalidArgumentException
     * 
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    LineReader::readPaginatedLines(string $file_path, int $per_page, int $page, string $page_name = 'page');
    ```

    &nbsp;

    🚨 **Exceptions**:

    - **readPaginatedLines** throws **\FruiVita\LineReader\Exceptions\FileNotReadableException** if don't have read permission on the file or it can't be found
    - **readPaginatedLines** throws **\InvalidArgumentException** if per_page or page is less than 1

⬆️ [Back](#table-of-contents)

&nbsp;

## Testing and Continuous Integration

```bash
composer analyse
composer test
composer coverage
```

⬆️ [Back](#table-of-contents)

&nbsp;

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed in each version.

⬆️ [Back](#table-of-contents)

&nbsp;

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for more details on how to contribute.

⬆️ [Back](#table-of-contents)

&nbsp;

## Code of conduct

To ensure that everyone is welcome to contribute to this open-source project, please read and follow the [Code of Conduct](CODE_OF_CONDUCT.md).

⬆️ [Back](#table-of-contents)

&nbsp;

## Security Vulnerabilities

Please see [security policy](/../../security/policy) how to report security vulnerabilities or flaws.

⬆️ [Back](#table-of-contents)

&nbsp;

## Support and Updates

The latest version will receive support and updates whenever the need arises. The others will receive updates for 06 months after being replaced by a new version and then discontinued.

| Version | PHP     | Release    | End of Life |
|---------|---------|------------|-------------|
| 1.0     | ^8.0    | dd-mm-yyyy | dd-mm-yyyy  |

🐛 Found a bug?!?! Open an **[issue](/../../issues/new?assignees=fcno&labels=bug%2Ctriage&template=bug_report.yml&title=%5BA+concise+title+for+the+bug%5D)**.

⬆️ [Back](#table-of-contents)

&nbsp;

## Roadmap

> ✨ Any new ideas?!?! Start a **[discussion](https://github.com/orgs/fruivita/discussions/new?category=ideas&title=[LineReader])**.

The following list contains identified and approved improvement needs that will be implemented in the first window of opportunity.

- [ ] n/a

⬆️ [Back](#table-of-contents)

&nbsp;

## Credits

- [Fábio Cassiano](https://github.com/fcno)

- [All Contributors](/../../contributors)

⬆️ [Back](#table-of-contents)

&nbsp;

## Thanks

👋 Thanks to the people and organizations below for donating their time to build the open-source projects that were used in this package.

- ❤️ [Laravel](https://github.com/laravel) for the packages:

  - [illuminate/collections](https://github.com/illuminate/collections)

  - [illuminate/pagination](https://github.com/illuminate/pagination)

  - [illuminate/support](https://github.com/illuminate/support)

- ❤️ [Orchestra Platform](https://github.com/orchestral) for the package [orchestral/testbench](https://github.com/orchestral/testbench)

- ❤️ [FriendsOfPHP](https://github.com/FriendsOfPHP) for the package [FriendsOfPHP/PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

- ❤️ [Nuno Maduro](https://github.com/nunomaduro) for the package [nunomaduro/larastan](https://github.com/nunomaduro/larastan)

- ❤️ [PEST](https://github.com/pestphp) for the packages:

  - [pestphp/pest](https://github.com/pestphp/pest)

  - [pestphp/pest-plugin-laravel](https://github.com/pestphp/pest-plugin-laravel)

- ❤️ [Sebastian Bergmann](https://github.com/sebastianbergmann) for the package [sebastianbergmann/phpunit](https://github.com/sebastianbergmann/phpunit)

- ❤️ [PHPStan](https://github.com/phpstan) for the packages:

  - [phpstan/phpstan](https://github.com/phpstan/phpstan)

  - [phpstan/phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules)

- ❤️ [ergebnis](https://github.com/ergebnis) for the package [ergebnis/composer-normalize](https://github.com/ergebnis/composer-normalize)

- ❤️ [Shivam Mathur](https://github.com/shivammathur) for the Github Action [shivammathur/setup-php](https://github.com/shivammathur/setup-php)

- ❤️ [GP](https://github.com/paambaati) for the Github Action [paambaati/codeclimate-action](https://github.com/paambaati/codeclimate-action)

- ❤️ [Stefan Zweifel](https://github.com/stefanzweifel) for the Github Actions:

  - [stefanzweifel/git-auto-commit-action](https://github.com/stefanzweifel/git-auto-commit-action)

  - [stefanzweifel/changelog-updater-action](https://github.com/stefanzweifel/changelog-updater-action)

💸 Some of these people or organizations have some products/services that can be purchased. If you can help them by buying one of them or becoming a sponsor, even for a short period, you will help the entire **open-source** community to continue developing solutions for everyone.

⬆️ [Back](#table-of-contents)

&nbsp;

## License

The MIT License (MIT). Please see the **[License File](../LICENSE.md)** for more information.

⬆️ [Back](#table-of-contents)
