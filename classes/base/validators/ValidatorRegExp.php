<?php

namespace classes\base\validators;

/**
 * Валидатор, осуществляющий проверку данных на соответствие регулярному выражению.
 *
 * Class ValidatorRegExp
 * @package classes\base\validators
 */
class ValidatorRegExp extends Validator
{
    /**
     * @param string $value Значение поля (данные)
     * @param array $params Параметры, переданные в валидатор
     * @return bool Пройдена или не пройдена валидация
     */
    public function run($value, $params = [])
    {
        $result = true;
        if(!isset($params['pattern'])) {
            $this->errorText = 'Не задан обязательный атрибут с именем pattern в методе rules()';
            return false;
        }

        //Проверка на соответствие регулярному выражению
        if (!preg_match($params['pattern'], $value)){
            if(empty($params['messageError'])){
                $this->errorText = 'Значение атрибута не соответствует регулярному выражению '.$params['pattern'];
            } else {
                $this->errorText = $params['messageError'];
            }

            $result = false;
        }

        return $result;
    }

}