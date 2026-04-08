# phpdb-adapter-sqlite

<!-- markdownlint-disable MD013 -->

This package provides [SQLite][sqlite] support for [php-db][php-db], which is a continuation of [laminas-db][laminas-db].

[![Build Status](https://github.com/php-db/phpdb-sqlite/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/php-db/phpdb-sqlite/actions/workflows/continuous-integration.yml)

## Prerequisites

You'll need the following to use the package:

- PHP 8.2 or above with the [PDO][php-pdo-extension] and [PDO SQLite][php-pdo-sqlite-extension] extensions
- [Composer][composer] installed globally

## Quick Start

To get started with the project, add it to your project with the following command:

```bash
composer require php-db/phpdb-sqlite
```

## Contributing

Please be sure to read the [contributor's guide](https://github.com/php-db/.github/blob/main/CONTRIBUTING.md) for general information on contributing.
This section outlines specifics for php-db.

### Test suites

The `phpunit.xml.dist` file defines two test suites, "unit test" and "integration test".
You can run one or the other using the `--testsuite` option to `phpunit`:

```bash
./vendor/bin/phpunit --testsuite "unit test" # unit tests only
./vendor/bin/phpunit --testsuite "integration test" # integration tests only
```

Unit tests do not require additional functionality beyond having the appropriate database extensions present and loaded in your PHP binary.

---

- File issues at <https://github.com/php-db/phpdb-sqlite/issues>
- Documentation is at <https://docs.php-db.dev>

[composer]: https://getcomposer.org
[laminas-db]: https://docs.laminas.dev/laminas-db/
[php-db]: https://github.com/php-db/phpdb
[php-pdo-extension]: https://www.php.net/manual/en/pdo.installation.php
[php-pdo-sqlite-extension]: https://www.php.net/manual/en/ref.pdo-sqlite.php
[sqlite]: https://sqlite.org/docs.html

<!-- markdownlint-enable MD013 -->
