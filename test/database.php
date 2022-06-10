<?php

require 'include/DBConnect.php';
$db = new DBConnect('unit_db', 'root', 'root', '');
// $db = new DBConnect('192.168.122.1', 'forgate_green', 'mEKCK4SSq8AKhGrLBYAj', 'processing_green');
 
$db->db_query("CREATE DATABASE `processing_green`;");
$db->db_query("CREATE DATABASE `forgate_green`;");
$db->db_query("CREATE USER 'forgate_green'@'%' IDENTIFIED BY 'mEKCK4SSq8AKhGrLBYAj';");
$db->db_query("GRANT ALL PRIVILEGES ON `forgate_green`.* TO 'forgate_green'@'%';");
$db->db_query("GRANT ALL PRIVILEGES ON `processing_green`.* TO 'forgate_green'@'%';");

$db->db_query("USE processing_green;");

$db->db_query("CREATE TABLE `exchange` (
  `transact` varchar(255) NOT NULL,
  `how` smallint(5) NOT NULL,
  `how_num` varchar(20) NOT NULL,
  `how_point` int(11) NOT NULL,
  `amount` decimal(24,10) NOT NULL,
  `fields` longtext NOT NULL,
  `try` int(11) NOT NULL,
  `next_try_date` datetime DEFAULT NULL,
  `last_operation` varchar(50) NOT NULL DEFAULT '',
  `next_operation` varchar(50) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `result` int(11) NOT NULL DEFAULT '-777',
  `result_text` longtext NOT NULL,
  `result_fields` longtext NOT NULL,
  `kernel_result` int(11) NOT NULL DEFAULT '-777',
  `kernel_result_text` varchar(1000) NOT NULL DEFAULT '',
  `form` int(11) NOT NULL,
  `id` bigint(15) unsigned NOT NULL,
  `operator_transact` varchar(255) NOT NULL DEFAULT '',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `amount_all` decimal(24,10) NOT NULL DEFAULT '0.0000000000',
  `fields_history` longtext NOT NULL,
  `chain` tinyint(1) NOT NULL DEFAULT '0',
  `priority` int(11) DEFAULT NULL,
  PRIMARY KEY (`transact`),
  KEY `id_exchange` (`id`),
  KEY `form_ex` (`form`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;");

// !!!!!!!!!!
$db->db_query("CREATE TABLE `exchange_arc` (
  `transact` varchar(255) NOT NULL,
  `how` smallint(5) NOT NULL,
  `how_num` varchar(10) NOT NULL,
  `how_point` int(11) NOT NULL,
  `amount` decimal(24,10) NOT NULL,
  `fields` longtext NOT NULL,
  `try` int(11) NOT NULL,
  `next_try_date` datetime DEFAULT NULL,
  `last_operation` varchar(50) NOT NULL,
  `next_operation` varchar(50) NOT NULL,
  `status` int(11) NOT NULL,
  `result` int(11) NOT NULL,
  `result_text` varchar(255) NOT NULL,
  `result_fields` longtext NOT NULL,
  `kernel_result` int(11) NOT NULL,
  `kernel_result_text` varchar(255) NOT NULL,
  `form` int(11) NOT NULL,
  `id` bigint(15) unsigned NOT NULL,
  `operator_transact` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `amount_all` decimal(24,10) NOT NULL,
  `fields_history` longtext,
  `chain` tinyint(1) NOT NULL DEFAULT '0',
  `priority` int(11) DEFAULT NULL,
  KEY `id_exchange` (`id`),
  KEY `form_ex` (`form`),
  KEY `transact` (`transact`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;");

$db->db_query("CREATE TABLE `multiprocessing` (
  `code` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `uid` bigint(15) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `protocol` smallint(5) unsigned NOT NULL,
  `dict_hash` char(32) DEFAULT NULL,
  `directory_status_last_text` varchar(500) DEFAULT NULL,
  `directory_status_last_datetime` datetime DEFAULT NULL,
  `keyt_out` char(20) DEFAULT NULL,
  `keyt_in` char(20) DEFAULT NULL,
  `keyt_profit` char(20) DEFAULT NULL,
  `keyt_loss` char(20) DEFAULT NULL,
  `balance_remote` decimal(24,10) DEFAULT NULL,
  `config_hash` varchar(255) DEFAULT NULL,
  `export_active` tinyint(1) DEFAULT '0' COMMENT 'Разрешить другим пользователям (моим субагентам, а также другим банкам и т.д.) проводить свои платежи через этот обработчик. Если эту опцию не включить, то только платежи с этого ID будут проходить.',
  `export_commission_access` tinyint(1) DEFAULT '1' COMMENT 'Взимать ли комиссию с других пользователей за платежи через этот шлюз',
  `export_commission_percent` decimal(5,2) DEFAULT NULL,
  `export_commission_percent_rule` tinyint(1) DEFAULT NULL,
  `export_commission_percent_type` tinyint(1) DEFAULT NULL,
  `export_commission_plusmoney` decimal(24,10) DEFAULT NULL,
  `export_commission_minlimit` decimal(24,10) DEFAULT NULL,
  `export_commission_maxlimit` decimal(24,10) DEFAULT NULL,
  `export_commission_rounding` varchar(25) DEFAULT NULL,
  `export_commission_add_how_commission` tinyint(1) DEFAULT NULL,
  `export_commission_excess_how_commission` tinyint(1) DEFAULT NULL,
  `export_compensation_access` tinyint(1) DEFAULT NULL COMMENT 'Выплачивать ли вознаграждение другим пользователям за платежи через этот шлюз',
  `export_compensation_percent` decimal(5,2) DEFAULT NULL,
  `export_compensation_percent_rule` tinyint(1) DEFAULT NULL,
  `export_compensation_percent_type` tinyint(1) DEFAULT NULL,
  `export_compensation_plusmoney` decimal(24,10) DEFAULT NULL,
  `export_compensation_minlimit` decimal(24,10) DEFAULT NULL,
  `export_compensation_maxlimit` decimal(24,10) DEFAULT NULL,
  `export_compensation_rounding` varchar(25) DEFAULT NULL,
  `registry_email` varchar(255) DEFAULT NULL,
  `removed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`code`),
  KEY `how` (`protocol`)
) ENGINE=InnoDB AUTO_INCREMENT=365 DEFAULT CHARSET=cp1251;");

$db->db_query("CREATE TABLE `multiprocessing_data` (
  `inc` int(11) NOT NULL AUTO_INCREMENT,
  `multiprocessing` int(11) NOT NULL,
  `param` int(11) NOT NULL,
  `ident` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `removed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`inc`),
  KEY `multiprocessing` (`multiprocessing`),
  KEY `removed` (`removed`)
) ENGINE=InnoDB AUTO_INCREMENT=3556 DEFAULT CHARSET=cp1251;");

