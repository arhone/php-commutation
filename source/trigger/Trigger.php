<?php declare(strict_types = 1);

namespace arhone\commutation\trigger;

/**
 * Триггер
 *
 * Class Trigger
 * @package arhone\commutation\trigger
 * @author Алексей Арх <info@arh.one>
 */
class Trigger implements TriggerInterface {

    /**
     * Конфигурация класса
     *
     * @var array
     */
    protected static $config = [
        'limit' => [
            'run' => 15
        ],
        'option' => [
            'break'  => false,
            'status' => true
        ]
    ];

    /**
     * Обработчики
     *
     * @var array
     */
    protected static $handler = [];

    /**
     * Опции триггеров
     *
     * @var array
     */
    protected static $option = [];

    /**
     * Счёт запусков
     *
     * @var array
     */
    protected static $runCount = [];

    /**
     * Номер добавляемого обработчика
     *
     * @var int
     */
    protected static $i = 1;

    /**
     * Trigger constructor.
     *
     * @param array $config
     */
    public function __construct (array $config = []) {

        $this->config($config);

    }

    /**
     * Устанавливает опции для триггера
     *
     * @param string|int $name
     * @param array $option ['status', 'break']
     * @return void
     */
    public function option ($name, array $option = []) : void {
        
        self::$option[$name] = array_merge(self::$option[$name] ?? [], $option);
        
    }

    /**
     * Добавляет обработчик
     *
     * @param string $pattern
     * @param callable $callback
     * @param array $option ['status', 'break']
     * @return string
     */
    public function add (string $pattern, callable $callback, array $option = []) : string {

        $pattern = strtolower($pattern);
        preg_match('#([.a-z_0-9:]+):#ui', '.:' . $pattern, $match);

        $handler = &self::$handler;

        foreach (explode(':', $match[1]) as $type) {

            $handler = &$handler[$type];

        }

        $name     = (string)($option['name'] ?? self::$i);
        $option   = array_merge($option, self::$option[$name] ?? []);
        $position = (string)(($option['position'] ?? 0) + (self::$i/1000));

        self::$option[$name] = [
            'name'     => $name,
            'pattern'  => $pattern,
            'break'    => $option['break'] ?? self::$config['option']['break'],
            'status'   => $option['status'] ?? self::$config['option']['status'],
            'position' => $position
        ];

        $handler['handler'][$position] = [
            'pattern'  => $pattern,
            'name'     => $name,
            'callback' => $callback
        ];

        self::$i++;

        return $name;

    }

    /**
     * Запуск триггера
     *
     * @param string $action
     * @param null $data
     * @return mixed|null
     * @throws \Exception
     */
    public function run (string $action, $data = null) {

        self::$runCount[$action] = empty(self::$runCount[$action]) ? 1 : self::$runCount[$action] + 1;
        if (isset(self::$runCount[$action]) && self::$runCount[$action] > self::$config['limit']['run']) {
            throw new \Exception('Trigger: обнаружена рекурсия события "' . $action . '"');
        }

        $action = strtolower($action);
        $store = &self::$handler;
        $pattern = explode(':', '.:' . $action);
        array_pop($pattern);

        $handlerList = [];
        foreach ($pattern as $type) {

            if (isset($store[$type])) {

                $store = &$store[$type];
                if (isset($store['handler'])) {

                    foreach ($store['handler'] as $id => $item) {

                        $handlerList[$id] = $item;

                    }

                }

            }

        }

        ksort($handlerList);
        foreach ($handlerList as $handler) {

            if (preg_match('#^' . $handler['pattern'] . '$#ui', $action, $match)) {

                if (self::$option[$handler['name']]['status']) {

                    $result = $handler['callback']->__invoke($match, $data, self::$option[$handler['name']]);

                    if ($result !== null) {

                        if (self::$option[$handler['name']]['break']) {

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
     * Порядок очереди триггера
     *
     * @param string $action
     * @return array
     */
    public function plan (string $action) : array {

        $action = strtolower($action);
        $data = [];
        $store = &self::$handler;
        $pattern = explode(':', '.:' . $action);
        array_pop($pattern);

        $handlerList = [];
        foreach ($pattern as $type) {

            if (isset($store[$type])) {

                $store = &$store[$type];
                if (isset($store['handler'])) {

                    foreach ($store['handler'] as $id => $item) {

                        $handlerList[$id] = $item;

                    }

                }

            }

        }

        ksort($handlerList);
        foreach ($handlerList as $handler) {

            if (preg_match('#^' . $handler['pattern'] . '$#ui', $action, $match)) {

                if (self::$option[$handler['name']]['status']) {
                    
                    $data[] = self::$option[$handler['name']];
                    
                }

            }

        }

        return $data;
        
    }
    
    /**
     * Метод для конфигурации класса
     * 
     * @param array $config
     * @return array
     */
    public function config (array $config) : array {

        return self::$config = array_merge(self::$config, $config);

    }

}