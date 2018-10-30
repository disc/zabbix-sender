# PHP Zabbix Sender 
[![Build Status](https://travis-ci.org/disc/zabbix-sender.svg?branch=master)](https://travis-ci.org/disc/zabbix-sender)
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)
[![Test Coverage](https://codeclimate.com/github/disc/zabbix-sender/badges/coverage.svg)](https://codeclimate.com/github/disc/zabbix-sender/coverage)

## Synopsis

Modern php implementation of Zabbix Sender Client.  
Support php versions PHP 5.3 and above.  
Working with Zabbix 2.0.8, 2.1.7+ and supports version 4.0.

## Code Example
Easy to use:
```
$sender = new \Disc\Zabbix\Sender('localhost', 10051);
$sender->addData('hostname', 'some.key.2', 0.567);
$sender->send();
```
See sample/sample.php


## Installation

Use composer for installation
`composer require disc/php-zabbix-sender`

## Tests

Run `vendor/bin/phpunit` for tests

## Contributors

Alexandr Hacicheant [a.hacicheant@gmail.com]

## License

[The MIT License (MIT)](LICENSE)
