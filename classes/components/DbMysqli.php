<?php

namespace classes\components;

use mysqli;

/**
 * Класс компонента, который осуществляет взаимодействие с базой данных.
 * Конпонент реализует интерфейс ComponentInterface.
 *
 * Class DbMysqli
 * @package classes\components
 */
class DbMysqli implements ComponentInterface
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
     * @var null|\mysqli Экземпляр соединения с базой данных
     */
    private $connect = null;

    /**
     * Метод, инициализирующий компонент.
     *
     * @return bool Компонент успешно инициализирован
     * @throws \Exception Не смогли установить соединение с БД
     */
    public function init()
    {
        $this->connect = new mysqli($this->host, $this->user, $this->password, $this->db, $this->port);
        if($this->connect->connect_errno){
            throw new \Exception('Не смогли установить соединение с БД: '.$this->connect->connect_error);
        }
        return true;
    }

    /**
     * Получения экземпляра класса PDO.
     *
     * @return null|mysqli
     */
    public function getPdo()
    {
        return $this->connect;
    }
}