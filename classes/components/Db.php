<?php

namespace classes\components;

use PDO; //Встроенный в php класс

/**
 * Класс компонента, который осуществляет взаимодействие с базой данных.
 * Конпонент реализует интерфейс ComponentInterface.
 *
 * Class Db
 * @package classes\components
 */
class Db implements ComponentInterface
{
    /**
     * @var string Host
     */
    public $host = '';
    /**
     * @var string Db
     */
    public $db = '';
    /**
     * @var string User
     */
    public $user = '';
    /**
     * @var string Password
     */
    public $password = '';
    /**
     * @var string Charset
     */
    public $charset = 'utf8';
    /**
     * @var integer Port
     */
    public $port = 3306;

    /**
     * @var null|PDO Экземпляр соединения с базой данных
     */
    private $pdo = null;

    /**
     * Метод, инициализирующий компонент.
     *
     * @return bool Компонент успешно инициализирован
     */
    public function init()
    {
        /**
         * Data Source Name
         */
        $dsn = 'mysql:host='.$this->host.':'.$this->port . ';dbname=' . $this->db . ';charset=' . $this->charset;
        /**
         * Дополнительные опции для соединения с базой данных
         */
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //Вывод исключений на экран
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC //Чтобы получить только аасоциативный массив данных (без индексного массива)
        ];
        /** @var PDO pdo Создаем экземпляр соединения с базой данных */
        $this->pdo = new PDO($dsn, $this->user, $this->password, $options);
        return true;
    }

    /**
     * Получения экземпляра класса PDO.
     *
     * @return null|PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }
}