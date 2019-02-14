<?php declare(strict_types = 1);

namespace arhone\commutation\trigger;

/**
 * Триггер
 *
 * Interface TriggerInterface
 * @package arhone\commutation\trigger
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
     * @return void
     */
    public function option ($name, array $option) : void;
    
    /**
     * Добавляет обработчик
     *
     * @param string $pattern
     * @param callable $callback
     * @param array $option ['status', 'break']
     * @return string
     */
    public function add (string $pattern, callable $callback, array $option = []) : string;
    
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
    public function plan (string $action) : array;

    /**
     * Метод для конфигурации класса
     * 
     * @param array $config
     * @return array
     */
    public function config (array $config) : array;

}