# PHP Zabbix Sender [![Build Status](https://travis-ci.org/disc/zabbix-sender.svg?branch=master)](https://travis-ci.org/disc/zabbix-sender)

## Synopsis

Modern php implementation of Zabbix Sender Client.  
Support php versions PHP 5.3 and above.  
Working with Zabbix 2.0.8 and 2.1.7+ versions.

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

Alexandr Hacicheant [discmd@ya.ru]

## License

MIT
