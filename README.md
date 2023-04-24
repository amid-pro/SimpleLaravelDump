# SimpleLaravelDump

![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/amid-pro/SimpleLaravelDump)
![GitHub Release Date](https://img.shields.io/github/release-date/amid-pro/SimpleLaravelDump)
![GitHub top language](https://img.shields.io/github/languages/top/amid-pro/SimpleLaravelDump)

## Introduction
Simple dump of MySql/Sqlite/PostgreSql databases

## Install
```php
composer require amid.pro/simple-laravel-dump
```

## Use

```php
//basic usage
artisan db:dump

//options
artisan db:dump --dump_path=PATH // Absolute path with trailing slash, default: %laravel%/database/dump/
                --db_name=DB_NAME // Default is .env value
                --db_user=DB_USER // Default is .env value
                --db_pass=DB_PASSWORD // Default is .env value
                --db_host=DB_HOST // Default is .env value
                --db_port=DB_PORT // Default is .env value
                --db_connection=DB_CONNECTION // Default is .env value
                
//code
Artisan::call('db:dump', [
    '--db_name' => ***
]);
```
