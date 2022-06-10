<?php

require 'vendor/autoload.php';
require __DIR__.'/include/const.php';
require __DIR__.'/include/DBConnect.php';
require __DIR__.'/include/Selenoid.php';
require __DIR__.'/include/Logger.php';

define('LOGS_DIR', __DIR__.'/logs/'.time());