<?php
/**
 * Метод соответствует стандарту PSR-0 и занимается подключением
 * необходимых для работы приложения классов в автоматическом режиме.
 *
 * @param string $className Имя подключаемого класса
 * @return bool Существует ли файл с классом
 */
function autoload($className)
{
    $className = ltrim($className, '\\'); //удаляет пробелы (или другие символы) из начала строки
    $fileName = ''; //Имя файла
    $namespace = ''; //Пространство имен

    //В случае наличия определенного пространства имен
    if($lastNsPos = strrpos($className, '\\')){
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    //Итоговый путь к файлу с классом для подключения
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    //Существует ли файл
    if(file_exists($fileName)) {
        //Если да, подключаем его с помощью метода require_once
        require_once $fileName;
        return true;
    }
    return false;
}

//Регистрируем наш автозагрузчик классов
spl_autoload_register('autoload');

