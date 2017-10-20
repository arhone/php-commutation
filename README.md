# Trigger
Управление событиями и их обработчиками

Trigger позволяет создавать событийные приложения.

# Применение
Триггер можно использовать для управления маршрутами (Router)

Можете сами формировать шаблон запроса, например для запросов через веб сервер, можно указывать HTTP:path, 
а для запросов через консоль console:path 

```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

$Trigger->add('HTTP:GET:/home.html', function () {
    return 'hello word'; 
});

echo $Trigger->run('HTTP:GET:/home.html');
```
 
Можно внедрять посредников (Middleware)

```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

// Проверяем если пользователь не авторизован
$Trigger->add('HTTP:GET:/home.html', function () {
    if (User::id() == false) {
        return 'Нужно авторизироваться';
    }
});

// Или выводим приветствие
$Trigger->add('HTTP:GET:/home.html', function () {
    return 'Привет'; 
});

// Пользователь зашёл по HTTP типа GET на страницу /home.html
echo $Trigger->run('HTTP:GET:/home.html');
``` 

Можно реагировать на события (Observer)

```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

// Очищаем кэш новостей
$Trigger->add('module.news.add', function ($data) {
    Cache::clear('module.news');
});

// Событие что была добавлена новость с id 100
$Trigger->run('module.news.add', [
    'id' => 100
]);
``` 
 
Можно обрабатывать запросы.

```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

// Пишем кэш в Redis
$Trigger->add('cache:set', function ($data) {
    CacheRedis::set($data['key'], $data['value']);
});

// Пишем в файл на всяких случай
$Trigger->add('cache:set', function ($data) {
    CacheRedis::set($data['key'], $data['value']);
});

// Генерируем команду на очистку кэша
$Trigger->run('cache:set', [
    'key' => 'ключ', 
    'value' => 'данные для кэширования'
]);
``` 

```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

// Берём кэш из редиса, если сервер редиса доступен
$Trigger->add('cache:get', function ($data) {
    if (CacheRedis::status() == true) {
        return CacheRedis::get($data['key']);    
    }
}, true); // Третий параметр break, остановит стек, если обработчик что то вернул (не null)

// Если редис ничего не вернул, то запустится следующий обработчик и вернёт кэш из файла
$Trigger->add('cache:get', function ($data) {
   return CacheFile::get($data['key']);
});

// Генерируем команду на получение кэша
$Trigger->run('cache:set', [
    'key' => 'ключ'
]);
``` 

Можно обрабатывать данные стеком обработчиков
```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

$Trigger->add('hello', function ($string) {
    return $string . ' мой';
});
$Trigger->add('hello', function ($string) {
    return $string . ' дорогой';
});
$Trigger->add('hello', function ($string) {
    return $string . ' друг';
});

echo $Trigger->run('hello', 'Привет'); // Привет мой дорогой друг
``` 