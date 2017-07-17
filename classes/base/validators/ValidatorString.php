<?php

namespace classes\base\validators;

/**
 * Валидатор, осуществляющий проверку данных строкового поля.
 *
 * Class ValidatorString
 * @package classes\base\validators
 */
class ValidatorString extends Validator
{
    /**
     * @param string $value Значение поля (данные)
     * @param array $params Параметры, переданные в валидатор
     * @return bool Пройдена или не пройдена валидация
     */
    public function run($value, $params = [])
    {
        $result = true;

        //Проверка на тип string
        if(!is_string($value)){
            $this->errorText = 'Поле не является строкой';
            $result = false;
        }

        //Если задан параметр min, то осуществляем проверку данных на минимальное количество символов
        if(isset($params['min']) && is_integer($params['min'])){
            if(mb_strlen($value) < $params['min']) {
                $this->errorText = 'Длина текста задана меньше допустимой. Минимум: '.$params['min'];
                $result = false;
            }
        }

        //Если задан параметр max, то осуществляем проверку данных на максимальное количество символов
        if(isset($params['max']) && is_integer($params['max'])){
            if(mb_strlen($value) > $params['max']) {
                $this->errorText = 'Длина текста задана больше допустимой. Максимум: '.$params['max'];
                $result = false;
            }
        }

        return $result;
    }

}