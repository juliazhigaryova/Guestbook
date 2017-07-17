<?php

require_once 'autoload.php';
use classes\base\App;

//Считываем основную конфигурацию приложения
$config = require_once(App::BASE_DIR.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'main.php');
//Запускаем приложение, run() - единая входная точка веб-приложения
App::run($config);