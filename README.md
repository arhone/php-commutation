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

$Trigger->handler('HTTP:GET:/home.html', function () {
    return 'hello word'; 
});

echo $Trigger->event('HTTP:GET:/home.html');
```
 
Можно внедрять посредников (Middleware)

```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

// Проверяем если пользователь не авторизован
$Trigger->handler('HTTP:GET:/home.html', function () {
    if (User::id() == false) {
        return 'Нужно авторизироваться';
    }
});

// Или выводим приветствие
$Trigger->handler('HTTP:GET:/home.html', function () {
    return 'Привет'; 
});

// Пользователь зашёл по HTTP типа GET на страницу /home.html
echo $Trigger->event('HTTP:GET:/home.html');
``` 

Можно реагировать на события (OBServer)

```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

// Очищаем кеш новостей
$Trigger->handler('module.news.add', function ($data) {
    Cache::clear('module.news');
});

// Событие что была добавлена новость с id 100
$Trigger->event('module.news.add', [
    'id' => 100
]);
``` 
 
Можно обрабатывать запросы.

```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

// Пишем кеш в Redis
$Trigger->handler('cache:set', function ($key, $data) {
    CacheRedis::set($key, $data);
});

// Пишем в файл на всяких случай
$Trigger->handler('cache:set', function ($key, $data) {
    CacheFile::set($key, $data);
});

// Генерируем команду на очистку кеша
$Trigger->event('cache:set', 'ключ', 'данные для кеширования');
``` 

```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

// Берём кеш из редиса, если сервер редиса доступен
$Trigger->handler('cache:get', function ($key, $data) {
    if (CacheRedis::status() == true) {
        return CacheRedis::get($key, $data);    
    }
});

// Если редис ничего не вернул, то запустится следующий обработчик и вернёт кеш из файла
$Trigger->handler('cache:get', function ($key, $data) {
   return CacheFile::get($key, $data);
});

// Генерируем команду на получение кеша
$Trigger->event('cache:set', 'ключ');
``` 

Можно обрабатывать данные стеком обработчиков
```php
<?php
use arhone\trigger\Trigger;

$Trigger = new Trigger();

$Trigger->handler('hello', function ($string) {
    return $string . ' мой';
});
$Trigger->handler('hello', function ($string) {
    return $string . ' дорогой';
});
$Trigger->handler('hello', function ($string) {
    return $string . ' друг';
});

// Третий параметр разрешает stack обработку
echo $Trigger->event('hello', 'Привет', true); // Привет мой дорогой друг
``` 