<?php

require '../../inc.php';
require '../web.php';
    
    $db = new DBConnect(MYSQL_EXCHANGE_HOST, MYSQL_EXCHANGE_LOGIN, MYSQL_EXCHANGE_PASSWORD, MYSQL_EXCHANGE_DB, $logger);
    
    $cab = new Web($db);
    $params = $cab->get_refer($_GET['transact']);
    print_r($params);
    $p = [];
    foreach($params as $key => $val) {
        $p[$key] = $val;
    }
    
	echo('<!DOCTYPE html>
	<html lang="en">
	<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="script.js"></script>
    <title>Document</title>
	</head>
	<body>
    <h1>Merchant Page</h1>
    <script>
        const data = '.json_encode($p, JSON_UNESCAPED_UNICODE).';
    </script>
	</body>
	</html>');