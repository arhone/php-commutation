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
     * @param string $action
     * @param callable $callback
     */
    public function handler (string $action, callable $callback) {

        self::$handler[$action][] = $callback;

    }

    /**
     * Запуск обработчиков события
     *
     * @param string $action
     * @param mixed $data
     * @return mixed
     */
    public function run (string $action, $data = null) {

        foreach (self::$handler as $key => $handlerList) {

            if (preg_match('~^' . $key . '$~ui', $action, $match)) {

                foreach ($handlerList as $callback) {

                    if ($response = $callback->__invoke($match, $data)) {

                        return $response;

                    }

                }

            }

        }

    }

    /**
     * Обработка события стеком обработчиков
     *
     * @param string $action
     * @param mixed $data
     * @return mixed
     */
    public function stack (string $action, $data = null) {

        foreach (self::$handler as $key => $handlerList) {

            if (preg_match('~^' . $key . '$~ui', $action, $match)) {

                foreach ($handlerList as $callback) {

                    $data = $callback->__invoke($match, $data);

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