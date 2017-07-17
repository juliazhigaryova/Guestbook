<?php

namespace classes\base;

/**
 * Основной класс приложения.
 * Он занимается инициализацией компонентов приложения.
 * Данный класс хранит внитри себя экземпляр приложения (паттерн SingleTon).
 *
 * Class App
 * @package classes\base
 */
class App
{
    //SingleTon
    /**
     * @var null|App $app Экземпляр приложения
     */
    static public $app = null;
    /**
     * @var array Компоненты
     */
    protected $components = [];

    /**
     * @var string Пространство имен, где находятся компоненты приложения
     */
    protected $componentsNamespace = 'classes\components';

    /**
     * Базовая дирректория приложения - classes
     */
    const BASE_DIR = __DIR__ . DIRECTORY_SEPARATOR . '..';

    /**
     * @param string $name Название компонента
     * @return bool Существует ли компонент в массиве
     */
    function __isset($name)
    {
        return key_exists($name, $this->components);
    }

    /**
     * @param string $name Имя компонента
     * @return mixed|null Возвращает экземпляр компонента или null
     */
    function __get($name)
    {
        return (key_exists($name, $this->components)) ? $this->components[$name] : null;
    }

    /**
     * @param string $name Название компонента
     * @param string $value Экземпляр компонента
     * @return bool Добавлен ли новый компонент
     */
    function __set($name, $value)
    {
        if (!key_exists($name, $this->components)) {
            $this->components[$name] = $value;
            return true;
        }
        return false;
    }

    /**
     * Создает экземпляр приложения.
     *
     * @param array $config Параметры для конфигурации всего приложения
     * @return App экземпляр приложения
     */
    static public function run($config = [])
    {
        //Если еще не созадан экземпляр приложения
        if (self::$app == null) {
            self::$app = new App();
            //Если в конфигурационном файле присутствует хотя бы один компонент
            if (!empty($config['components'])) {
                self::$app->initComponents($config['components']); //Инициализация компонентов
            }
        }
        return self::$app;
    }

    /**
     * Инициализируем компоненты приложения.
     *
     * @param array $config Параметры конфигурации компонентов
     * @return bool
     * @throws \Exception Если отсутствует хотя бы один класс компонента,
     * который описан в конфигурационном файле
     */
    public function initComponents($config = [])
    {
        //Перебираем все компоненты, описанные в конфигурации приложения
        foreach ($config as $componentId => $componentParams)
        {
            //Получаем полное название класса компонента (с учетом namespace)
            $classComponent = $this->componentsNamespace . '\\' . $componentParams['class'];
            //Существует ли класс компонента
            if(class_exists($classComponent)){
                //Если да, то создаем экземпляр класса компонента
                $this->{$componentId} = new $classComponent;
            } else {
                //Если нет, то бросаем исключение
                throw new \Exception('Класса компонента '. $classComponent . ' не существует!');
            }

            //Перебираем параметра каждого компонента и устанавиваем их
            //Для каждого компонента
            foreach ($componentParams as $paramName => $paramValue)
            {
                //Если такой параметр существует у компонента
                //Все переопределяемые параметры (свойства класса)
                // должны иметь модификатор доступа public
                if(property_exists($this->{$componentId}, $paramName)){
                    //Устанавливаем заданное в конфигурации значение параметра для компонента
                    $this->{$componentId}->{$paramName} = $paramValue;
                }
            }
            //Выполняем инициализацию компонента
            $this->{$componentId}->init();
        }
        return true;
    }

    /**
     * Метод получения компонента.
     *
     * @param string $name Имя компонента
     * @return mixed|null Экземпляр компонента или null
     */
    public function getComponent($name)
    {
        if (!empty($this->components[$name])) {
            return $this->components[$name];
        }
        return null;
    }
}