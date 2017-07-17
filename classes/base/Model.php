<?php

namespace classes\base;

use classes\base\validators\Validator;

/**
 * Абстрактный класс, который содержит методы для работы с моделью данных.
 * Класс реализует ORM (object-relational mapping) - объектно-реляционное отображение данных.
 * Благодаря классу разработчику больше не нужно писать SQL запросы,
 * ему достаточно работать с методами, которые производят все необходимые манипуляции с базой данных.
 *
 * Также, класс отвечает за валидацию данных, список валидаторов
 * класс получает из метода rules() текущей модели.
 *
 * Class Model
 * @package classes\base
 */
abstract class Model
{
    /** @var array Ошибки после валидации данных */
    private $_errors;

    /** @var string Название пространства имен, где хранятся валидаторы */
    private $_namespaceValidator = '\classes\base\validators';

    /** @var bool Новая запись в базе данных или редактирование существующей */
    public $isNewRecord = false;

    /**
     * Получение названия таблицы в базе данных, с которой работает модель
     *
     * @return string Название таблицы, с которой связана определенная модель приложения
     */
    abstract static public function getTable();

    /**
     * Метод возвращает список свойств модели, которые учавствуют при взаимодействии с базой данных (вставка, обновление данных).
     * Если название свойства модели не добавить в данный массив, то оно не будет изменено при сохранении данных.
     *
     * @return array Массив свойств модели, которые учавствуют при взаимодействии с базой данных (вставка, обновление данных)
     */
    abstract static public function getAttributes();

    /**
     * Метод возвращает список полей и имен валидаторов, которые должны быть применены к свойствам модели.
     *
     * Пример:
     * static public function rules()
     * {
     * return [
     * ['name', 'ValidatorRequired'], //Применяем валидатор ValidatorRequired к свойству модели name
     * ['name', 'ValidatorString', 'min'=>2, 'max' => 30], //'min'=>2, 'max' => 30 - дополнительные параметры, которые можно передать в валидатор
     * ];
     * }
     *
     * @return array Правила валидации
     */
    abstract static public function rules();

    /**
     * Метод добавляет новую ошибку валидации в массив $this->_errors.
     *
     * @param string $attribute Название атрибута модели, который не прошел валидацию
     * @param string $error Текст ошибки, появившейся при валидации поля модели
     */
    public function addError($attribute, $error = '')
    {
        $this->_errors[$attribute][] = $error;
    }

    /**
     * Метод добавляет сразу несколько ошибок валидации в массив $this->_errors.
     *
     * @param array $items Массив с ошибками валидации
     */
    public function addErrors(array $items)
    {
        foreach ($items as $attribute => $errors) {
            //Если у нас в $items есть еще вложенный массив
            if (is_array($errors)) {
                foreach ($errors as $error) {
                    //Перебираем его и добовляем конкретную ошибку валидации
                    $this->addError($attribute, $error);
                }
            } else {
                //Если это не массив, то добавляем конкретную ошибку валидации
                $this->addError($attribute, $errors);
            }
        }
    }

    /**
     * Метод очищает ошибки, возникшие при валидации данных модели.
     *
     * @param null|string $attribute Атрибут модели, ошибки валидации которого необходимо очистить
     */
    public function clearErrors($attribute = null)
    {
        if ($attribute === null) {
            $this->_errors = [];
        } else {
            //Удаляем вложенный массив с ошибками для конкретного атрибута модели
            unset($this->_errors[$attribute]);
        }
    }

    /**
     * Метод возвращает все ошибки валидации в виде массива.
     * Есть возможность вернуть ошибки валидации для конкретного атрибута модели.
     *
     * @param null|string $attribute Имя атрибута модели, ошибки которого необходимо вернуть
     * @return array Массив с ошибками
     */
    public function getErrors($attribute = null)
    {
        if ($attribute === null) {
            return $this->_errors === null ? [] : $this->_errors;
        } else {
            return (isset($this->_errors[$attribute])) ? $this->_errors[$attribute] : [];
        }
    }

    /**
     * @param null|string $attribute Имя атрибута модели, ошибки которого необходимо проверить
     * @return bool Есть ли ошибки для всей модели или для конкретного атрибута
     */
    public function hasErrors($attribute = null)
    {
        return $attribute === null ? !empty($this->_errors) : isset($this->_errors[$attribute]);
    }

    /**
     * Метод производит валидацию данных модели.
     *
     * @param bool $clearErrors Если флаг установлен в true до валидации будет вызван метод $this->clearErrors();
     * @return bool Прошла ли модель валидацию
     * @throws \Exception Ошибки при создании валидаторов и при отсутствии свойств в валидируемом классе
     */
    public function validate($clearErrors = true)
    {
        if ($clearErrors) {
            //Очищаем ошибки в массиве $this->_error от предыдущей валидации
            $this->clearErrors();
        }

        //Вызываем метод до начала валидации
        if (!$this->beforeValidate()) {
            return false; //Не начинаем валидацию
        }

        //Валидация
        /** @var array $rules Получаем правила из модели посредством ПСС (позднего статического связывания) */
        $rules = static::rules();

        //Перебираем массив правил валидации
        foreach ($rules as $rule) {
            //array_shift получает значение первого элемента массива и удаляет его из массива
            /** @var string $attributeName Название атрибута модели, валидацию которого необходимо провести */
            $attributeName = array_shift($rule);
            /** @var string $validatorClassName Название класса валидатора */
            $validatorClassName = array_shift($rule);

            //Проверяем, есть ли свойство $attributeName в валидируемой модели
            if (!property_exists($this, $attributeName)) {
                //Если свойство отсутствует, вызываем исключение
                //get_called_class() возвращает имя класса, экземпляр которого вызвал этот метод (валидации)
                throw new \Exception('Свойство с именем ' . $attributeName . ' отсутствует в классе ' . get_called_class());
            }

            //Полный путь к классу валидатора
            $pathClass = $this->_namespaceValidator . '\\' . $validatorClassName;
            //Существует ли такой класс в системе
            if (class_exists($pathClass)) {
                /** @var Validator $validatorInstace Экземпляр класса валидатора */
                $validatorInstace = new $pathClass;
                //Вызываем метод run класса валидатора, который и осуществляет процесс валидации
                //$rule - массив правил, попадает в параметр $params метода run()
                if (!$validatorInstace->run($this->$attributeName, $rule)) {
                    //Добавляем ошибку, если свойство модели не прошло валидацию
                    $this->addError($attributeName, $validatorInstace->getError());
                }
            } else {
                //Если класс валидатора отсутствует в системе
                throw new \Exception('Класса валидатора ' . $pathClass . ' не существует!');
            }
        }

        //Вызываем метод после валидации
        $this->afterValidate();


         //Возвращаем true, если ошибок нет и валидация пройдена, иначе false
        return !$this->hasErrors();
    }

    /**
     * Метод вызывается до начала валидации.
     * Если он возвращает true - валидация выполняется,
     * иначе - процесс валидации проведен не будет.
     *
     * @return bool Начинать ли валидацию или вернуть false
     */
    public function beforeValidate()
    {
        return true;
    }

    /**
     * Метод выполняется после валидации.
     */
    public function afterValidate()
    {

    }

    /**
     * Метод устанавливает свойства модели из $attributes.
     *
     *
     * @param array $attributes Атрибуты модели со значениями
     */
    public function setAttributes(array $attributes)
    {
        //Получаем массив с правилами валидации из модели
        $rules = static::rules();

        /** @var array $rulesUnique Уникальные (без повторений) свойства, для которых есть правила валидации,
         * следовательно, мы их можем массово присвоить модели.
         */
        $rulesUnique = [];
        foreach ($rules as $rule) {
            //Ищем имя свойства модели (из правила) в массиве $rulesUnique
            if (array_search($rule[0], $rulesUnique) === false) {
                //Если имени свойства не нашли, добавляем его
                array_push($rulesUnique, $rule[0]);
            }
        }


        //Присваиваем свойствам модели, которые найдены в правилах валидации (минимум 1 раз)
        //переданные в аргументе $attributes значения
        foreach ($attributes as $key => $attributeValue) {
            if (array_search($key, $rulesUnique) !== false) {
                //$key - название свойства модели
                $this->$key = $attributeValue;
            }
        }
    }
}