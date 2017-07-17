<?php

namespace classes\base\helpers;

/**
 * Вспомогательный класс для работы с Html.
 *
 * Class Html
 * @package classes\base\helpers
 */
class Html
{
    /**
     * Метод экранирует небезопасные символы в $content и возвращает безопасные для вставки на страницу данные
     *
     * @param string $content Данные
     * @return string Безопасные для вывода данные
     */
    public static function encode($content)
    {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE);
    }

    /**
     * Процесс обратный encode($content)
     *
     * @param string $content Данные
     * @see encode($content)
     * @return string Небезопасные для вывода данные
     */
    public static function decode($content)
    {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }
}