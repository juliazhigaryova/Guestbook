<?php

namespace classes\base\validators;

/**
 * Валидатор, осуществляющий проверку данных на обязательное заполнение поля.
 *
 * Class ValidatorRequired
 * @package classes\base\validators
 */
class ValidatorRequired extends Validator
{
    protected $errorText = 'Обязательное поле не заполнено';

    /**
     * @param string $value Значение поля (данные)
     * @param array $params Параметры, переданные в валидатор
     * @return bool Пройдена или не пройдена валидация
     */
    public function run($value, $params = [])
    {
        return !empty($value);
    }

}