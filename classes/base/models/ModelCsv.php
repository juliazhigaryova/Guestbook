<?php

namespace classes\base\models;

use classes\base\Model;
use classes\base\App;

/**
 * Класс для работы с данными в формате csv.
 * Класс реализует выборку, добавление, обновление и удаление данных из файлов csv.
 *
 * Class ModelCsv
 * @package classes\base\models
 */
abstract class ModelCsv extends Model
{
    /**
     * Разделитель данных в файлах csv
     */
    const DELITEL_DATA_CSV = ';';

    /**
     * Метод осуществляет подключение к файлу csv.
     *
     * @param string $mode Режим работы с файлом (чтение - r, дозапись - a, перезапись - w)
     * @param null $fileName Имя файла, если оно не передано, то имя файла берется из метода модели getTable()
     * @return resource
     * @throws \Exception
     */
    static protected function connect($mode = 'a+', $fileName = null)
    {
        //Если имя файла не передано, то оно берется из метода модели
        if ($fileName === null) {
            $fileName = static::getTable() . '.csv';
        }

        //Полный путь к дирректории, которая используется для сохранения csv файлов
        $dirData = App::BASE_DIR . DIRECTORY_SEPARATOR . '/data';
        //Полный путь к файлу scv включая дирректорию
        $fullFileName = $dirData . DIRECTORY_SEPARATOR . $fileName;

        //Если файла csv не существует, создаем его
        if (!file_exists($fullFileName)) {
            //Создает новый файл csv
            touch($fullFileName);
        }

        //Пробуем открыть файл в режиме $mode
        $res = fopen($fullFileName, $mode);

        //Если файл не удалось открыть, бросаем исключение
        if ($res === false) {
            throw new \Exception('Файл ' . $fullFileName . ' не удается открыть');
        }

        //Возвращаем дескриптор открытого файла
        return $res;
    }

    /**
     * Метод закрывает ранее установленное соединение с файлом
     *
     * @param resource $res Дескриптор открытого файла
     */
    static protected function close($res)
    {
        if ($res !== null) {
            //Закрываем соединение с файлом
            fclose($res);
        }
    }

    /**
     * Метод выбирает из файла csv данные и возращает из как массив.
     * В массиве находятся экземпляры конкретных моделей, которые связаны с данными.
     *
     * @param null $conditions Условие
     * @param array $order Сортировка данных
     * @return array Массив данных, который включает экземпляры моделей с данными
     */
    static public function findAll($conditions = null, $order = [])
    {
        //Устанавливаем соединение с файлом. Режим - чтение
        $connect = self::connect('r');

        //Массив для данных, которые вернет метод
        $dataArray = [];

        //Свойства модели, которые учавствуют в работе с csv
        $attributesModel = static::getAttributes();

        //В цикле while построчно читаем файл, преобразуя строку csv в массив
        //Цикл работает, пока в файле не закончатся строки
        while (($strItems = fgetcsv($connect, 10000, self::DELITEL_DATA_CSV)) !== false) {
            //Количество элементов в массиве - строка в csv преобразованная в массив
            $count = count($strItems);

            //Создаем экземпляр модели
            $model = new static();

            //Заполняем свойства созданной модели данными
            for ($i = 0; $i < $count; $i++) {
                $model->{$attributesModel[$i]} = $strItems[$i];
            }
            //Добавляем модель, заполненную данными, в массив
            array_push($dataArray, $model);
        }

        //Закрываем соединение с файлов csv
        self::close($connect);
        return $dataArray;
    }

    /**
     * Метод генерирует новый id для вставки данных в файл.
     * ID получается как последний id в файле + 1.
     *
     * @return null
     */
    static protected function generateID()
    {
        $connect = self::connect('r');
        $lastId = null;

        while (($strItems = fgetcsv($connect, 10000, self::DELITEL_DATA_CSV)) !== false) {
            $lastId = $strItems[0];
        }

        self::close($connect);
        //Увеличеваем текущий ID на 1 и возвращаем
        return ++$lastId;
    }

    /**
     * Метод выбирает одно сообщение из файла по первичному ключу
     *
     * @param integer $pk ID (первичный ключ)
     * @return false|static Возвращает модель, заполненную данными или false, если ничего не найдено
     */
    static public function findOne($pk)
    {
        $connect = self::connect('r');
        $attributesModel = static::getAttributes();

        //Данные для заполнения модели
        $searchDataPk = false;

        while (($strItems = fgetcsv($connect, 10000, self::DELITEL_DATA_CSV)) !== false) {
            //Сравниваем ID каждой записи с переданным ID
            if($strItems[0] == $pk)
            {
                //Если данные найдены, присваеваем их $searchDataPk
                $searchDataPk = $strItems;
                break;
            }
        }
        self::close($connect);

        //Если ничего не найдено, возвращаем false
        if($searchDataPk === false){
            return false;
        }

        $count = count($searchDataPk);
        $model = new static();

        for ($i = 0; $i < $count; $i++) {
            $model->{$attributesModel[$i]} = $searchDataPk[$i];
        }

        return $model;
    }

    /**
     * Метод ищет одну запись в файле с учетом условия и возвращает ее.
     *
     * @param array $conditions Условие
     * @return false|static Возвращает модель, заполненную данными или false, если ничего не найдено
     */
    static public function findOneByCondition(array $conditions)
    {
        $connect = self::connect('r');
        $attributesModel = static::getAttributes();

        $searchDataPk = false;

        $conditionsCsvKey = null; //Ключ внутри файла
        $conditionsCsvValue = null; //Значение, которое мы ищем внутри файла

        //Ищем номера условий и значений
        foreach ($conditions as $key => $value)
        {
            //Если условие допустимо, переопределяем имя ключа и значение для дальнейшего поиска
            if(($keyByValue = array_search($key, $attributesModel)) !== false){
                $conditionsCsvKey = $keyByValue;
                $conditionsCsvValue = $value;
            }
        }

        while (($strItems = fgetcsv($connect, 10000, self::DELITEL_DATA_CSV)) !== false) {
            //Условие, по которому выбираются данные
            if(isset($strItems[$conditionsCsvKey]) && $strItems[$conditionsCsvKey] == $conditionsCsvValue)
            {
                $searchDataPk = $strItems;
            }
        }
        self::close($connect);

        if($searchDataPk === false){
            return false;
        }

        $count = count($searchDataPk);
        $model = new static();

        for ($i = 0; $i < $count; $i++) {
            $model->{$attributesModel[$i]} = $searchDataPk[$i];
        }

        return $model;
    }

    /**
     * Метод обновляет 1 запись с первичным ключем ($pk).
     *
     * @param integer $pk Первичный ключ
     * @param array $attributes Данные для изменения
     * @return integer Длина вставленной в файл обновленной строки или false в случае неудачи
     */
    static public function updateOne($pk, $attributes = [])
    {
        $connect = self::connect('r');
        $dataArray = []; //Данные для записи до и после изменения
        $attributesModel = static::getAttributes();

        $elChange = []; //Изменяемый элемент
        while (($strItems = fgetcsv($connect, 10000, self::DELITEL_DATA_CSV)) !== false) {
                array_push($dataArray, $strItems);

                if($strItems[0] == $pk){
                    //В переменную помещаем ссылку на изменяемый элемент в массиве
                    $elChange = &$dataArray[count($dataArray) - 1];
                }
        }
        self::close($connect);


        $attributesNumber = []; //Преобразуем ключи атрибутов в номера, так как в csv нет именных ключей
        //Ищем номера условий и значений
        foreach ($attributes as $key => $value)
        {
            //Если условие допустимо
            if(($keyByValue = array_search($key, $attributesModel)) !== false){
                //Добавляем в массив числовое значение ключа со значением
                $attributesNumber[$keyByValue] = $value;
            }
        }

        $res = false; //Результат работы функции по изменению данных
        //Перебираем массив с новыми данными, чтобы изменить их у изменяемой строки
        foreach ($attributesNumber as $key => $value)
        {
            //Если в изменяемом элементе есть данные с таким номером, то изменяем из на новые
            if(isset($elChange[$key])){
                $elChange[$key] = $value;
                $res = true; //Данные успешно изменены
            }
        }


        $connect = self::connect('w');
        foreach ($dataArray as $item)
        {
            fputcsv($connect, $item, self::DELITEL_DATA_CSV);
        }
        self::close($connect);
        return $res;
    }

    /**
     * Метод удаляет 1 строку из файла, которая соответствует первичному ключу $pk.
     *
     * @param integer $pk Первичный ключ
     * @return bool Удалось ли удалить строку из файла
     */
    static public function deleteOne($pk)
    {
        $connect = self::connect('r');

        $dataArray = [];

        $res = false;

        while (($strItems = fgetcsv($connect, 10000, self::DELITEL_DATA_CSV)) !== false) {
            //Все, что не соответствует записи с переданным ключем, не добавляем в новый массив
            if($strItems[0] != $pk) {
                array_push($dataArray, $strItems);
            } else {
                $res = true;
            }
        }
        self::close($connect);


        $connect = self::connect('w');
        foreach ($dataArray as $item)
        {
            fputcsv($connect, $item, self::DELITEL_DATA_CSV);
        }
        self::close($connect);
        return $res;
    }

    /**
     * Метод добавляет 1 запись с новыми данными в файл.
     *
     * @param array $attributes Массив с данными
     * @return integer Длина вставленной в файл строки или false в случае неудачи
     */
    static public function insertOne($attributes = [])
    {
        array_unshift($attributes, self::generateID());
        $connect = self::connect();
        //Записываем новую строку в файл
        $result = fputcsv($connect, $attributes, self::DELITEL_DATA_CSV);
        self::close($connect);
        return $result;
    }
}