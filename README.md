# PHP CI/CD Lab 1

![PHP CI](https://github.com/vadymdmytryk23-ship-it/php-ci-cd-lab1/actions/workflows/php-ci.yml/badge.svg)

Laboratory work 1 for Web Programming.

## Description

This project demonstrates automatic testing of PHP applications using GitHub Actions.

## Technologies

- PHP 8.2
- Composer
- PHPUnit
- Laravel Pint
- GitHub Actions

## Features

- Addition and subtraction of numbers
- Unit testing with PHPUnit
- Code formatting checks with Laravel Pint
- Automatic CI pipeline

## Running tests

Install dependencies:

```bash
composer install
```

Check code formatting:

```bash
./vendor/bin/pint --test
```

Run unit tests:

```bash
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/Unit
```
