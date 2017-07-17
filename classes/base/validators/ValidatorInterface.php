<?php

namespace classes\base\validators;

/**
 * Интерфейс, который должен реализовывать любой валидатор (реализует абстрактный класс)
 * с обязательным добавлением метода в каждый валидатор.
 *
 *
 * Interface ValidatorInterface
 * @package classes\base\validators
 */
interface ValidatorInterface
{
    public function run($value, $params = []);
}