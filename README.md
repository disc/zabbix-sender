# PHP Zabbix Sender [![Build Status](https://travis-ci.org/disc/zabbix-sender.svg?branch=master)](https://travis-ci.org/disc/zabbix-sender)

## Synopsis

Modern php implementation of zabbix_sender utility.  
Working on PHP 5.3+.  
Works with Zabbix 2.0.8 and 2.1.7+ versions.

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

Aleksandr Khachikyants [disc@mydbg.ru]

## License

MIT
