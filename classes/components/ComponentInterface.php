<?php

namespace classes\components;

/**
 * Интерфейс, который должен реализовывать каждый компонент.
 *
 * Interface ComponentInterface
 * @package classes\components
 */
interface ComponentInterface
{
    /**
     * Метод инициализации компонента.
     */
    public function init();
}