<?php

require __DIR__.'/include/DBConnect.php';

$xml = simplexml_load_file(__DIR__.'/phpunit.xml');

foreach ($xml->php->const as $const) {
	define($const['name'], $const['value']);
}

$db = new DBConnect(MYSQL_EXCHANGE_HOST, MYSQL_EXCHANGE_LOGIN, MYSQL_EXCHANGE_PASSWORD, MYSQL_EXCHANGE_DB);

$db->db_query("DELETE FROM exchange");
$db->db_query("DELETE FROM exchange_arc");
$db->db_query("DELETE FROM multiprocessing");
$db->db_query("DELETE FROM multiprocessing_data");

echo "Cleaning completed";