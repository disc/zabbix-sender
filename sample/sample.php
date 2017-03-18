<?php

error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';

$sender = new \Disc\Zabbix\Sender('localhost', 10051);
$sender->addData('hostname', 'some.key.1', 0.567);
$sender->addData('hostname', 'some.key.2', 0.567);
$sender->send();

var_export($sender->getResponse());