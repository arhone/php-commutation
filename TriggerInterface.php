<?php declare(strict_types = 1);
namespace arhone\trigger;

/**
 * Триггер
 *
 * Interface TriggerInterface
 * @package arhone\trigger
 */
interface TriggerInterface {

    /**
     * Trigger constructor.
     *
     * @param array $config
     */
    public function __construct (array $config = []);

    /**
     * Добавляет обработчик
     * 
     * @param $action
     * @param callable $callback
     * @param bool $break
     */
    public function add ($action, callable $callback, bool $break = false);

    /**
     * Запуск триггера
     *
     * @param $action
     * @param null $data
     * @return mixed
     */
    public function run ($action, $data = null);

    /**
     * Метод для конфигурации класса
     * 
     * @param array $config
     * @return array
     */
    public function config (array $config) : array;

}