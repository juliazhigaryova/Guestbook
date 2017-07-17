<?php

namespace classes\base;

/**
 * Абстрактный класс, который содержит методы, необходимые для работы пользовательских контроллеров.
 *
 * Class Controller
 * @package classes\base
 */
abstract class Controller
{
    /**
     * @var string Имя файла макета в папке views
     */
    public $layout = 'layout';
    /**
     * @var array Параметры, которые контроллер передает в представление для вывода
     */
    public $params = [];

    /**
     * Метод для передачи данных в конкретное представление и его отображения.
     *
     * @param string $view Имя файла представления без расширения .php
     * @param array $params Параметры, которые контроллер передает в представление для вывода
     * @return mixed Html-код представления
     * @throws \Exception Макет не найден в папке views
     */
    public function render($view, $params = [])
    {
        //Сохраняем переданные параметры в свойстве класса (для отображения во view)
        if(is_array($params)){
            $this->params = $params;
        }
        $layout = $this->layout;
        //Абсолютный путь к файлу с макетом
        $fileLayout = App::BASE_DIR . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $layout . '.php';
        //Абсолютный путь к файлу предствления (фрагмент, который вставляется в макет)
        $fileView = App::BASE_DIR . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
        //Проверяем на наличие файла макета и представления в папке view
        if(file_exists($fileLayout) && file_exists($fileView))
        {
            //Включаем буферизацию вывода. Это необходимо, чтобы операторы вывода (echo, print)
            //и html-код не выводились сразу на экран, а сохранялись в памяти.
            //Это позволяет сохранять вывод на экран в переменную.
            ob_start();
            //Подключаем макет
            require ($fileLayout);
            /** @var string $layoutCode В переменную сохраняем данные из буфера и очищаем его */
            $layoutCode = ob_get_clean();

            //Включаем буферизацию вывода.
            ob_start();
            //Подключаем представление
            require ($fileView);
            /** @var string $viewCode В переменную сохраняем данные из буфера и очищаем его */
            $viewCode = ob_get_clean();
            /** @var string $data Вставляем в код макета код представления и сохраняем результирующий код в переменной */
            $data = str_replace('<div class="content"></div>', $viewCode, $layoutCode);

            return $data;
        } else {
            throw new \Exception('Layout не существует! ' . $fileLayout);
        }
    }

    /**
     * Метод осуществляет перенаправление пользователя на $url
     *
     * @param string $url URL-адрес перенаправления пользователя
     * @param int $code Код ответа сервера
     */
    public function redirect($url, $code = 301)
    {
        //Делегируем действие компоненту request, т. к. от отвечает за маршруты пользователя
        App::$app->request->redirect($url, $code);
    }

    /**
     * Метод обновляет страницу.
     */
    public function refresh()
    {
        $url = $_SERVER['REQUEST_URI'];
        //Задействуем метод redirect для обновления страницы
        $this->redirect($url, 302);
    }
}