<?php

namespace classes\components;

/**
 * Класс компонента Request.
 * Компонент занимается разбором URL-адреса.
 * Компонент отвечает за вызов определенного действия контроллера.
 *
 * Class Request
 * @package classes\components
 */
class Request implements ComponentInterface
{
    /** @var string Имя контроллера по умолчанию */
    public $controller = 'main';

    /** @var string Имя действия контроллера по умолчанию */
    public $action = 'index';

    /** @var string Пространство имен, где у нас находятся классы контроллера */
    public $namespaceController = '\classes\controllers';

    /**
     * Метод выполняет переадресацию пользователя на $url.
     *
     * @param string $url Адрес, на который необходимо выполнить редирект
     * @param int $code Код ответа сервера
     */
    public function redirect($url, $code = 301)
    {
        //Отправляем серверу заголовок Location, который отвечает за переадресацию
        header('Location: ' . $url, true, $code);
    }

    /**
     * Метод возвращает массив данных, переданных на сервер методом POST.
     *
     * @param string $nameArray Название массива, в котором находятся данные внутри $_POST
     * @return array Массив данных, предварительно очищенных от опасных конструкций
     */
    public function postData($nameArray)
    {
        //Массив для данных, которые будут возвращены
        $result = [];

        if(isset($_POST[$nameArray]) && is_array($_POST[$nameArray])){
            foreach ($_POST[$nameArray] as $key => $value)
            {
                //Вызываем метод $this->safeData, куда передаем данные для очищения от опасных конструкций
                $result[$key] = $this->safeData($value);
            }
        }
        return $result;
    }

    /**
     * Метод возвращает массив данных, переданных на сервер методом GET.
     *
     * @param string $nameArray Название массива, в котором находятся данные внутри $_GET
     * @return array Массив данных, предварительно очищенных от опасных конструкций
     */
    public function getData($nameArray)
    {
        //Массив для данных, которые будут возвращены
        $result = [];

        if(isset($_GET[$nameArray]) && is_array($_GET[$nameArray])){
            foreach ($_GET[$nameArray] as $key => $value)
            {
                //Вызываем метод $this->safeData, куда передаем данные для очищения от опасных конструкций
                $result[$key] = $this->safeData($value);
            }
        }
        return $result;
    }

    /**
     * Метод производит очистку переданных данных от опасных конструкций.
     *
     * @param string $value Данные
     * @return string Очищенные от опасных конструкций данные
     */
    private function safeData($value)
    {
        //Вырезаем html теги
        $value = strip_tags($value);
        //Все опасные символы заменяем их кодами
        $value = htmlspecialchars($value);
        return $value;
    }

    /**
     * Основной метод компонента.
     * Он вызывается самим приложением сразу после создания экземпляра компонента.
     * Метод разбирает URL и вызывает нужный метод контроллера.
     */
    public function init()
    {
        //URL
        $uri = $_SERVER['REQUEST_URI'];

        //Разбиваем URL по символу "/"
        $path = explode('/', $uri);

        //Если у нас в URL есть и имя контроллера, и имя действия
        if(count($path) == 2) {
            //Не пустое ли у нас имя контроллера
            if(mb_strlen($path[1]) > 0) {
                //Если не пустое, то присваеваем имя свойству $this->controller
                $this->controller = $path[1];
            }
        } else {
            //Если у нас в массиве $path колично элементов отлично от 2-х (имя контроллера и действия)
            if(mb_strlen($path[1]) > 0){
                $this->controller = $path[1];
            }
            if(mb_strlen($path[2]) > 0){
                //Имя действия берем из элемента $path[2]
                $actionName = $path[2];

                //Если есть GET-параметры после знака вопроса, то удаляем из из имени действия
                if(mb_strpos($actionName, '?') !== false)
                {
                    //Удаляем все что у нас после знака "?", включая знак вопроса из имени действия
                    $actionName = substr($actionName, 0, mb_strpos($actionName, '?'));
                }
                $this->action = $actionName;
            }
        }
        //Вызываем метод, который создаст экземпляр класса контроллера и вызовет действие
        $this->callController();
    }

    /**
     * Метод создает экземпляр класса контроллера и вызовет действие.
     *
     * @throws \Exception Класса контроллера не существует
     */
    protected function callController()
    {
        //Полный путь к классу контроллера
        $classController = $this->namespaceController . '\\' . ucwords($this->controller) . 'Controller';

        //Имя действия контроллера с приставкой action
        $actionMethod = 'action' . ucwords($this->action);

        //Существует ли класс контроллера
        if(class_exists($classController)){
            //Если да, то создаем экземпляр класса контроллера
            $controllerInstance = new $classController;
        } else {
            //Если нет, то бросаем исключение
            throw new \Exception('Класса контроллера '. $classController . ' не существует!');
        }

        //Проверяем, содержит ли контроллер действие, которое можно вызвать
        if(method_exists($controllerInstance, $actionMethod))
        {
            //Если да, то вызываем его
            //call_user_func_array - вызывает метод $actionMethod внутри экземпляра класса $controllerInstance
            call_user_func_array([$controllerInstance, $actionMethod], []);
        } else {
            //Если нет, то бросаем исключение
            throw new \Exception('Такого действия не существует');
        }
    }
}