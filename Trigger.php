<?php declare(strict_types = 1);
namespace arhone\trigger;

/**
 * Триггер
 *
 * Class Trigger
 * @package arhone\trigger
 */
class Trigger {

    /**
     * Конфигурация класса
     *
     * @var array
     */
    protected static $config = [];

    /**
     * Обработчики
     *
     * @var array
     */
    protected static $handler = [];

    /**
     * Trigger constructor.
     *
     * @param array $config
     */
    public function __construct (array $config = []) {

        $this->config($config);

    }

    /**
     * Добавляет обработчик
     *
     * @param string|int $action
     * @param callable $callback
     */
    public function handler ($action, callable $callback) {

        self::$handler[$action][] = $callback;

    }

    /**
     * Запуск обработчиков события
     *
     * @param string|int $action
     * @param null $data
     * @param bool $stack
     * @return mixed
     */
    public function event ($action, $data = null, $stack = false) {

        foreach (self::$handler as $key => $handlerList) {

            if (preg_match('~^' . $key . '$~ui', $action, $match)) {

                foreach ($handlerList as $callback) {

                    if ($stack) {

                        $data = $callback->__invoke($match, $data);

                    } elseif ($response = $callback->__invoke($match, $data)) {

                        return $response;

                    }

                }

            }

        }

        return $data;

    }

    /**
     * Метод для конфигурации класса
     *
     * @param array $config
     */
    public function config (array $config) {

        self::$config = array_merge(self::$config, $config);

    }

}