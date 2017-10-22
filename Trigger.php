<?php declare(strict_types = 1);
namespace arhone\trigger;

/**
 * Триггер
 *
 * Class Trigger
 * @package arhone\trigger
 */
class Trigger implements TriggerInterface {

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
     * @param $action
     * @param callable $callback
     * @param bool $break
     */
    public function add ($action, callable $callback, bool $break = false) {

        self::$handler[$action][] = [
            'callback' => $callback,
            'break'    => $break
        ];

    }

    /**
     * Запуск триггера
     *
     * @param $action
     * @param null $data
     * @return mixed
     */
    public function run ($action, $data = null) {

        foreach (self::$handler as $key => $handlerList) {

            if (preg_match('~^' . $key . '$~ui', $action, $match)) {

                foreach ($handlerList as $handler) {

                    $result = $handler['callback']->__invoke($match, $data);

                    if ($result !== null) {

                        if ($handler['break']) {

                            return $result;

                        } else {

                            $data = $result;

                        }

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