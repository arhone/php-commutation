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
    protected static $config = [
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
     * @var array
     */
    protected static $call = [];

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
     * @return array
     */
    public function option ($name, array $option = []) {
        
        return self::$option[$name] = array_merge(self::$option[$name] ?? [], $option);
        
    }

    /**
     * Добавляет обработчик
     *
     * @param string $pattern
     * @param callable $callback
     * @param array $option ['status', 'break']
     * @return int|string
     */
    public function add (string $pattern, callable $callback, array $option = []) {

        $pattern = strtolower($pattern);
        preg_match('#([.a-z_0-9:]+):#ui', '.:' . $pattern, $match);

        $handler = &self::$handler;

        foreach (explode(':', $match[1]) as $type) {

            $handler = &$handler[$type];

        }

        $name     = $option['name'] ?? self::$i;
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
     * @return null
     * @throws \Exception
     */
    public function run (string $action, $data = null) {

        self::$call[$action]++;
        if (isset(self::$call[$action]) && self::$call[$action] > 10) {
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