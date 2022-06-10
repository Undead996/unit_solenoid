<?php

define('MYSQL_EXCHANGE_HOST'     , '192.168.122.1');
define('MYSQL_EXCHANGE_PORT'     , '3306');
define('MYSQL_EXCHANGE_DB'       , 'processing_green');
define('MYSQL_EXCHANGE_LOGIN'    , 'forgate_green');
define('MYSQL_EXCHANGE_PASSWORD' , 'mEKCK4SSq8AKhGrLBYAj');

define('MYSQL_FORGATE_HOST'        , '192.168.122.1');
define('MYSQL_FORGATE_PORT'        , '3306');
define('MYSQL_FORGATE_DB'          , 'forgate_green');
define('MYSQL_FORGATE_LOGIN'       , 'forgate_green');
define('MYSQL_FORGATE_PASSWORD'    , 'mEKCK4SSq8AKhGrLBYAj');

define('MYSQL_DEV_EXCHANGE_HOST'     , '192.168.122.1');
define('MYSQL_DEV_EXCHANGE_PORT'     , '3306');
define('MYSQL_DEV_EXCHANGE_DB'       , 'processing_dev');
define('MYSQL_DEV_EXCHANGE_LOGIN'    , 'gate_dev');
define('MYSQL_DEV_EXCHANGE_PASSWORD' , 'TUOnENDGgvbWx91jnocI');

define('PROTOCOL_HASHCONNECT', 300);
define('PROTOCOL_PAYMEGA',     380);
define('PROTOCOL_GUAVA',       400);
define('PROTOCOL_QIWI',        265);

define('PROTOCOL_DEV_GUAVASANDBOX',        401);

# https://bit.paypoint.pro/company/personal/user/6/tasks/task/view/44945/
define('BALANCE_RANDOM_POSITIVE_AMOUNT', 110);
define('BALANCE_ZERO',                   120);
define('BALANCE_RANDOM_NEGATIVE_AMOUNT', 130);
define('BALANCE_INCORRECT_ANSWER',       210);
define('BALANCE_ELEMENT_MISSING',        220);
define('BALANCE_WRONG_SIGN',             230);
define('BALANCE_WRONG_ENCODE',           240);
define('BALANCE_WRONG_NUMBER',           250);
define('BALANCE_TIMEOUT_70',             310);
define('BALANCE_TIMEOUT_130',            320);
define('BALANCE_TIMEOUT_FULL',           390);
define('BALANCE_PROTOCOL_FATAL',         410);
define('BALANCE_PROTOCOL_NONFATAL',      420);

define('HC',                             [
    'no_3ds' => [
        '1' => '4111111111111111',
    ],
    '3ds_1' => [
        '1' => '4916798373459761'
    ]
]);
