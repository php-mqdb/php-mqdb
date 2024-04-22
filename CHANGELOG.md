# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [4.1.2] - 2024-04-22
[4.1.2]: https://github.com/php-mqdb/php-mqdb/compare/4.1.1...4.1.2
### Changed
- Force reconnection when handle auto-reconnection

## [4.1.1] - 2024-04-22
[4.1.1]: https://github.com/php-mqdb/php-mqdb/compare/4.1.0...4.1.1
### Changed
- Fix typo on connection name when use auto-reconnection

## [4.1.0] - 2024-04-22
[4.1.0]: https://github.com/php-mqdb/php-mqdb/compare/4.0.0...4.1.0
### Added
- Now can pass ConnectionFactory (from eureka/component-database) to PDORepository to handle auto-reconnection
- Add some integration test for PDO, PDO with ConnectionFactory & DBAL repository
### Changed
- Some CS fix after php-cs-fixer update
- Dependencies check is now in dependencies, not at global level


## [4.0.0] - 2024-02
[4.0.0]: https://github.com/php-mqdb/php-mqdb/compare/3.0.1...4.0.0
### Added
- PHP-CS-Fixer dev dependencies
- Add ramsey/uuid dependency
### Changed
- Now use Uuid v7 for message id rather than pseudo random uuid.
- Fix DBALMessageRepository to handle changes with DBAL v3 (deprecation) / v4
- Minor code update
- Remove DBAL from mandatory dependencies. Now in dev dependencies
- Update SQL schema to improve types & indices (fast for lots of messages, but no more date feature)
- Update some examples
### Deleted
- PHPCS dev dependencies
- Remove PHP 7.4 support

----

## [3.0.1] - 2022-10-14
[3.0.1]: https://github.com/php-mqdb/php-mqdb/compare/3.0.0...3.0.1
### Changed
- Update retry condition during ExecuteQuery in DBALMessageRepository

## [3.0.0] - 2022-10-10
[3.0.0]: https://github.com/php-mqdb/php-mqdb/compare/2.5.0...3.0.0
### Added
- CI (phpstan, unit test, github workflow...)
- Makefile to run some CI command directly
- Fix code style & phpstan errors
- Allow usage of Doctrine\DBAL 2.x & 3.x
- Test can now use more easily DBAL by switch config file
### Changed
- In Repository, now use appropriate methods if using DBAL connection (no more deprecation)
- 

----

## [2.5.0] - 2022-09-30
[2.5.0]: https://github.com/php-mqdb/php-mqdb/compare/2.4.0...2.5.0
### Added
- Force reconnection when deadlock is detected


## [2.4.0] - 2022-08-22
[2.4.0]: https://github.com/php-mqdb/php-mqdb/compare/2.3.0...2.4.0
### Added
- Add publishOrSkip()


## [2.3.0] - 2021-09-02
[2.3.0]: https://github.com/php-mqdb/php-mqdb/compare/2.2.0...2.3.0
### Changed
- Now use random_int instead of mt_rand for ID generator


## [2.2.0] - 2020-12-07
[2.2.0]: https://github.com/php-mqdb/php-mqdb/compare/2.1.0...2.2.0
### Added
- Can specify callback function to handle specifics cases for message content merging
- Update interfaces & client according to the new feature
- Add 'ext-json' as requirement in composer.json
- Add example file


## [2.1.0] - 2020-01-31
[2.1.0]: https://github.com/php-mqdb/php-mqdb/compare/2.0.0...2.1.0
### Added
- New method to replay messages stuck with pending status
### Changed
- Change update date when reserve messages to process.


## [2.0.0] - 2019-09-19
[2.0.0]: https://github.com/php-mqdb/php-mqdb/compare/1.0.1...2.0.0
### Added
 - Use strict mode
 - Add new TableConfig class to "configure" field, table name & ordering by default
 - Add some new exceptions
 - Add QueryBuilder & QueryBuilderFactory
 - Now more SOLID code
   - Factories are now instantiable
   - Can redefine table config through dedicated class instead of override repository class
### Changed
 - Optimisations:
   - Now remove not necessary ordering when filter on same field that order field
   - Replace IN by = in where when have only one value (more understandable query)
 - Some fields are now optional:
   - entity_id, date_expiration & date_availability
   - When an optional field is not present in field list, it will be excluded from queries


----

## [1.0.1] - 2018-04-24
[1.0.1]: https://github.com/php-mqdb/php-mqdb/compare/1.0.0-beta...1.0.1
### Added
 - Add support of "_" in topic name.

## [1.0.0-beta] - 2017-11-06
[1.0.0-beta]: https://github.com/php-mqdb/php-mqdb/compare/0.8.0...1.0.0-beta
### Added
 - Ordering messages by priority & dates
 - Can update message based on topic & entity id

---- 

## [0.8.0] - 2017-10-13
[0.7.0]: https://github.com/php-mqdb/php-mqdb/compare/0.7.0...0.8.0
### Added
 - Handle Mysql Gone away exception & try to reconnect to db with DBAL driver
 


## [0.7.0] - 2017-09-08
### Added
 - Fix documentation (Enumerator\Status)
 - Add Client::countMessage(Filter $filter) method (and repository implementation)
