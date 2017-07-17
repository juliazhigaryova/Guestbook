<?php

namespace classes\base\models;

use classes\base\Model;
use classes\base\App;

/**
 * Класс для работы с базой данных посредством MySQLi.
 *
 * Class Db
 * @package classes\components
 */
abstract class ModelMysqli extends Model
{
    /**
     * Model constructor.
     */
    function __construct()
    {
        //Если свойство первичного ключа в модели пустое, значит перед нами новая запись
        if (empty($this->{static::getPrimaryKey()})) {
            $this->isNewRecord = true; //новая запись в БД
        }
    }

    /**
     * Метод возвращает Название первичного ключа модели.
     * Первичный ключ необходим, чтобы однозначно идентифицировать запись в таблице бызы данных.
     * По умолчанию название ключа - id.
     * Метод может быть переопределен в модели.
     *
     * @return string Название первичного ключа модели
     */
    static public function getPrimaryKey()
    {
        return 'id';
    }

    /**
     * Метод осуществляет поиск в таблице данных, связанной с моделью и возвращает все найденные записи,
     * которые соответствуют условию $conditions с применением сортировки $order.
     *
     * @param null|string $conditions Условия выборки данных из таблицы
     * @param array $order Сортировка данных
     * @return array Пустой массив (если данные не найдены) или массив экземпляров моделей с заполненными данными
     */
    static public function findAll($conditions = null, $order = [])
    {
        $sqlCondition = ''; //Условие выборки данных для добавление в SQL
        $sqlOrder = ''; //Условие сортировки, по умолчанию сортировка отсутствует

        //Определяем условие сортировки для добавления в SQL запрос
        if (!empty($order)) {
            $key = array_keys($order)[0];
            $sqlOrder = ' ORDER BY ' . $key . ' ' . $order[$key];
        }

        //Определяем условие выборки данных для добавление в SQL
        if (is_array($conditions) && count($conditions) > 0) {

            $sqlCondition .= ' WHERE messageid=' . $conditions['messageid'];
        }

        //Осуществляем SQL запрос к базе данных
        /** @var \mysqli $con */
        $con = App::$app->db->getPdo();
        $res = $con->query('SELECT * FROM ' . static::getTable() . $sqlCondition . $sqlOrder);
        $data = [];
        while ($dataStr = mysqli_fetch_object($res, get_called_class())) {
            array_push($data, $dataStr);
        }
        return $data;
    }

    /**
     * Метод возращает экземпляр модели, заполненной данными из таблицы базы данных.
     *
     * @param string $pk Значение первичного ключа для поиска одной записи
     * @return false|object False (если данные не найдены) или экземпляр модели с заполнением данными
     */
    static public function findOne($pk)
    {
        $pk = (integer) $pk;

        /** @var \mysqli $pdo Получаем соединение с базой данных */
        $pdo = App::$app->db->getPdo();

        $query = $pdo->query('SELECT * FROM ' . static::getTable() . ' WHERE ' . static::getPrimaryKey() . ' = ' . $pk);
        $data = mysqli_fetch_object($query, get_called_class());
        //Если данные получены, возвращаем их, иначе false
        return is_object($data) ? $data : false;
    }

    /**
     * Поиск одной записи в базе данных по условию $conditions.
     *
     * @param array $conditions Условия для выборки данных
     * @return false|mixed False (если данные не найдены) или экземпляр модели с заполнением данными
     */
    static public function findOneByCondition(array $conditions)
    {
        //Часть SQL запроса, условие WHERE
        $sqlCondition = '';
        //Если маасив с условиями не пустой
        if(count($conditions) > 0){
            $sqlCondition = ' WHERE ';
        }
        //Перебираем все условия массива и соединяем AND
        foreach ($conditions as $conditionKey => $conditionValue)
        {
            $sqlCondition.= $conditionKey . '=' . $conditionValue . ' AND ';
        }

        //Если есть условия, последние 4 символа 'AND ' необходимо удалить
        if(mb_strlen($sqlCondition) > 0){
            $sqlCondition = substr($sqlCondition, 0, -4);
        }

        /** @var \mysqli $pdo Получаем соединение с базой данных */
        $pdo = App::$app->db->getPdo();
        $query = $pdo->query('SELECT * FROM ' . static::getTable() .  $sqlCondition);
        $data = mysqli_fetch_object($query, get_called_class());
        //Если данные получены, возвращаем их, иначе false
        return is_object($data) ? $data : false;
    }

    /**
     * Метод обновляет одну запись из таблицы, найденную по первичному ключу.
     *
     * @param string $pk Значение первичного ключа для обновления конкретной записи в базе данных
     * @param array $attributes Массив с данными, которые необходимо вставить в таблицу
     * @return bool Удалось ли обновить данные
     * @throws \Exception Некорректные данные для добавления в БД
     */
    static public function updateOne($pk, $attributes = [])
    {
        $pk = (integer) $pk;
        /** @var \mysqli $pdo */
        $pdo = App::$app->db->getPdo();
        $attributesInClass = static::getAttributes();

        $existsAttributesSql = '';
        //Проверяем, чтобы не было ничего лишнего
        //Принадлежат ли атрибуты нашей модели, все что не принадлежит,
        //Отбрасываем, чтобы не добавлять в таблицу БД
        foreach ($attributes as $attributeKey => $attributeValue)
        {
            if(array_search($attributeKey, $attributesInClass) !== false)
            {
                $existsAttributesSql .= $attributeKey . '=\''.$attributeValue.'\', ';
            }
        }
        if(mb_strlen($existsAttributesSql) > 0){
            $existsAttributesSql = substr($existsAttributesSql, 0, -2); //Удаляем последние 2 символа ', '
        } else {
            throw new \Exception('Некорректные данные для добавления в БД');
        }

        $sql = "UPDATE " . static::getTable() . " SET " . $existsAttributesSql . " WHERE `id`=".$pk;
        $pdo->query($sql);
        return ($pdo->affected_rows > 0) ? true : false;
    }

    /**
     * Метод удаляет одну запись из таблицы, найденную по первичному ключу.
     *
     * @param string $pk Значение первичного ключа для обновления конкретной записи в базе данных
     * @return bool Удалось ли удалить данные
     */
    static public function deleteOne($pk)
    {
        $pk = (integer) $pk;
        /** @var \mysqli $pdo */
        $data = ['id' => $pk];
        $pdo = App::$app->db->getPdo();
        $sql = "DELETE FROM " . static::getTable() . " WHERE `id`=" . $pk;


        $pdo->query($sql);
        return ($pdo->affected_rows > 0) ? true : false;
    }

    /**
     * Метод добавляет одну новую запись в таблицу.
     *
     * @param array $attributes Массив с данными, которые необходимо вставить в таблицу
     * @return bool Добавлены ли данные в БД
     * @throws \Exception Некорректные данные для добавления в БД
     */
    static public function insertOne($attributes = [])
    {
        /** @var \mysqli $pdo */
        $pdo = App::$app->db->getPdo();
        $attributesInClass = static::getAttributes();

        $existsAttributesSql = '';
        //Проверяем, чтобы не было ничего лишнего
        //Принадлежат ли атрибуты нашей модели, все что не принадлежит,
        //Отбрасываем, чтобы не добавлять в таблицу БД
        foreach ($attributes as $attributeKey => $attributeValue)
        {
            if(array_search($attributeKey, $attributesInClass) !== false)
            {
                $existsAttributesSql .= $attributeKey . '=\''.$attributeValue.'\', ';
            }
        }
        if(mb_strlen($existsAttributesSql) > 0){
            $existsAttributesSql = substr($existsAttributesSql, 0, -2);
        } else {
            throw new \Exception('Некорректные данные для добавления в БД');
        }

        $sql = "INSERT INTO " . static::getTable() . " SET " . $existsAttributesSql;
        $pdo->query($sql);
        return ($pdo->affected_rows > 0) ? true : false;
    }
}