<?php

namespace classes\base\models;

use classes\base\App;
use classes\base\Model;

/**
 * Класс для работы с базой данных посредством PDO.
 *
 * Class ModelPdo
 * @package classes\base\models
 */
abstract class ModelPdo extends Model
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
        $data = App::$app->db->getPdo()->query('SELECT * FROM ' . static::getTable() . $sqlCondition . $sqlOrder)->fetchAll(\PDO::FETCH_CLASS, get_called_class());
        return $data;
    }

    /**
     * Метод готовит данные модели для сохранения или изменения в базе данных.
     *
     * @param array $allowed Список полей модели, которые связаны с таблицей данных @see getAttributes()
     * @param array $values Массив, в который будут положены имена параметров для связывания с подготовленным запросом.
     * @param array $source Сами данные модели, которые должны попасть в SQL запрос
     * @return string Параметры для вставки в SQL запрос
     */
    static private function pdoSet($allowed, &$values, $source = [])
    {
        $set = '';
        $values = [];
        foreach ($allowed as $field) {
            if (isset($source[$field])) {
                $set .= "`" . str_replace("`", "``", $field) . "`" . "=:$field, ";
                $values[$field] = $source[$field];
            }
        }
        //Перед возвратом строки с параметрами, вырезаем символы ", " в конце строки
        return substr($set, 0, -2);
    }


    /**
     * Метод возращает экземпляр модели, заполненной данными из таблицы базы данных.
     *
     * @param string $pk Значение первичного ключа для поиска одной записи
     * @return false|object False (если данные не найдены) или экземпляр модели с заполнением данными
     */
    static public function findOne($pk)
    {
        /** @var \PDO $pdo Получаем соединение с базой данных */
        $pdo = App::$app->db->getPdo();

        //Делаем подготовленный запрос
        $query = $pdo->prepare('SELECT * FROM ' . static::getTable() . ' WHERE ' . static::getPrimaryKey() . ' = :pk');

        //Выполняем подготовленный запрос с передачей первичного ключа в качестве единственного параметра
        $query->execute(
            [
                ':pk' => $pk
            ]
        );

        //Устанавливаем режим возврата данных в свойства модели, из которой вызван статический метод findOne($pk)
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class());

        //Сохраняем полученные данные в переменную
        $data = $query->fetch();

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
        /** @var \PDO $pdo */
        $pdo = App::$app->db->getPdo();

        //Свойства модели, которые мы можем изменять (учавствующие в работе с базой данных)
        $attributesInClass = static::getAttributes();

        //Массив для сохранения подготовленных для запроса параметров
        $data = [];

        //Делаем подготовленный запрос
        $query = $pdo->prepare('SELECT * FROM ' . static::getTable() . ' WHERE ' . self::pdoSet($attributesInClass, $data, $conditions));

        //Выполняем подготовленный запрос с передачей массива подготовленных параметров ($data)
        $query->execute($data);

        //Устанавливаем режим возврата данных в свойства модели, из которой вызван статический метод
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class());

        //Сохраняем полученные данные в переменную
        $data = $query->fetch();

        //Если данные получены, возвращаем их, иначе false
        return is_object($data) ? $data : false;
    }

    /**
     * Метод обновляет одну запись из таблицы, найденную по первичному ключу.
     *
     * @param string $pk Значение первичного ключа для обновления конкретной записи в базе данных
     * @param array $attributes Массив с данными, которые необходимо вставить в таблицу
     * @return int Количество строк, затронутых запросом
     */
    static public function updateOne($pk, $attributes = [])
    {
        /** @var \PDO $pdo */
        $pdo = App::$app->db->getPdo();
        $attributesInClass = static::getAttributes();
        $data = [];
        $sql = "UPDATE " . static::getTable() . " SET " . self::pdoSet($attributesInClass, $data, $attributes) . " WHERE " . static::getPrimaryKey() . "=:id";
        $pdoPrepare = $pdo->prepare($sql);
        $data['id'] = $pk;
        $pdoPrepare->execute($data);

        //Возвращаем количество строк в таблице базы данных, затронутых запросом
        return $pdoPrepare->rowCount();
    }

    /**
     * Метод удаляет одну запись из таблицы, найденную по первичному ключу.
     *
     * @param string $pk Значение первичного ключа для обновления конкретной записи в базе данных
     * @return int Количество строк, затронутых запросом
     */
    static public function deleteOne($pk)
    {
        /** @var \PDO $pdo */
        $data = ['id' => $pk];
        $pdo = App::$app->db->getPdo();
        $sql = "DELETE FROM " . static::getTable() . " WHERE " . static::getPrimaryKey() . "=:id";
        $pdoPrepare = $pdo->prepare($sql);
        $pdoPrepare->execute($data);

        //Возвращаем количество строк в таблице базы данных, затронутых запросом
        return $pdoPrepare->rowCount();
    }

    /**
     * Метод добавляет одну новую запись в таблицу.
     *
     * @param array $attributes Массив с данными, которые необходимо вставить в таблицу
     * @return bool Произведена ли вставка новой записи в таблицу
     */
    static public function insertOne($attributes = [])
    {
        /** @var \PDO $pdo */
        $pdo = App::$app->db->getPdo();
        $attributesInClass = static::getAttributes();
        $data = [];
        $sql = "INSERT INTO " . static::getTable() . " SET " . self::pdoSet($attributesInClass, $data, $attributes);
        $pdoPrepare = $pdo->prepare($sql);
        return $pdoPrepare->execute($data);
    }
}