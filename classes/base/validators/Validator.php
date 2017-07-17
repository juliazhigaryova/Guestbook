<?php

namespace classes\base\validators;

/**
 * Абстрактный класс, который должны наследовать все валидаторы
 * Класс реализует интерфейс ValidatorInterface, чтобы каждый валидатор не мог не реализовать метод run().
 *
 * Class Validator
 * @package classes\base\validators
 */
abstract class Validator implements ValidatorInterface
{
    /**
     * @var string Сообщение валидации
     */
    protected $errorText = 'Общая ошибка валидации';

    /**
     * Геттер, чтобы текст ошибки нельзя было изменить извне.
     * @return string Текст ошибки валидации
     */
    public function getError()
    {
        return $this->errorText;
    }
}