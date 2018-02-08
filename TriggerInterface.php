<?php declare(strict_types = 1);
namespace arhone\trigger;

/**
 * Триггер
 *
 * Interface TriggerInterface
 * @package arhone\trigger
 * @author Алексей Арх <info@arh.one>
 */
interface TriggerInterface {

    /**
     * Trigger constructor.
     *
     * @param array $config
     */
    public function __construct (array $config = []);

    /**
     * Устанавливает опции для триггера
     *
     * @param string|int $name
     * @param array $option
     * @return array
     */
    public function option ($name, array $option);
    
    /**
     * Добавляет обработчик
     *
     * @param string $pattern
     * @param callable $callback
     * @param array $option ['status', 'break']
     * @return int|string
     */
    public function add (string $pattern, callable $callback, array $option = []);
    
    /**
     * Запуск триггера
     *
     * @param string $action
     * @param null $data
     * @return mixed
     */
    public function run (string $action, $data = null);

    /**
     * Порядок очереди триггера
     * 
     * @param string $action
     * @return array
     */
    public function plan (string $action);

    /**
     * Метод для конфигурации класса
     * 
     * @param array $config
     * @return array
     */
    public function config (array $config) : array;

}