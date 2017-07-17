<?php

namespace classes\models;

use classes\base\models\ModelPdo as Model;
//use classes\base\models\ModelMysqli as Model;

/**
 * Модель "Сообщения"
 *
 * Class Messages
 * @package classes\models
 */
class Messages extends Model
{
    //Свойства модели (класса) соответствуют столбцам таблицы в базе данных
    public $id;
    public $name;
    public $text;
    public $date;

    /**
     *  Метод возвращает массив с правила валидации данных именно для этой модели.
     *
     * @return array Правила валидации данных в свойствах модели
     */
    static public function rules()
    {
        return [
            ['name', 'ValidatorRequired'],
            ['name', 'ValidatorString', 'min' => 2, 'max' => 30],
            [
                'name',
                'ValidatorRegExp',
                'pattern' => '/^[a-zа-яё]+$/ui',
                'messageError' => 'Имя должно состоять только из букв латинского и русского алфавита',
            ],
            ['text', 'ValidatorRequired'],
            ['text', 'ValidatorString', 'min' => 5, 'max' => 5000],
            ['date', 'ValidatorRequired'],
        ];
    }

    /**
     * Метод возвращает имя таблицы, с которой связана текущая модель.
     *
     * @return string Таблица в базе данных, с которой связана текущая модель
     */
    static public function getTable()
    {
        return 'gb_messages';
    }

    /**
     * Метод возвращает имена атрибутов модели (свойства класса), которые связаны со столбцами БД.
     *
     * @return array Атрибуты модели
     */
    static public function getAttributes()
    {
        return [
            'id',
            'name',
            'text',
            'date',
        ];
    }

    /**
     * Метод переопределяет базовый метод класса Model beforeValidate().
     * Он выполняется до валидации данных, и от его кода возврата зависит, продолжится ли валидация.
     *
     * @return bool Продолжать ли валидацию данных
     */
    public function beforeValidate()
    {
        //Устанавливаем свойство date равное кол-ву секунд, прошедших  с 1 января 1970 00:00:00 GMT) до текущего времени.
        $this->date = time();
        //Вызываем родительский метод
        return parent::beforeValidate();
    }

    /**
     * Метод возвращает все комментарии, которые связаны с текущей моделью.
     *
     * @return array Комментарии, связанные с текущем сообщением
     */
    public function getCommets()
    {
        return Comments::findAll(['messageid' => $this->id]);
    }
}