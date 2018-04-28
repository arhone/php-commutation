# Trigger

Обработчик событий (PHP 7)

Триггер - это совокупность события и вызванных им действий.

Триггер позволяет создавать событийные приложения.

Принцип работы триггера очень прост:
1) Вы добавляется обработчик какого то события
2) Запускаете это событие

Можно добавлять сколько угодно обработчиков на одно и тоже событие, в таком случае они последовательно обработают ваш запрос.

Триггер может пригодиться в различных задачах, например:
1) Вывод разного содержимого сайта по разным адресам. В качестве события вы указываете URI страницы, обработчик конкретной страницы возвращает конкретный результат.
2) Обработка команд из консоли или крона или через API других приложений, например telegram. На сервер приходит команда, обработчики на неё реагируют.
3) Создание зацепок. Например можно запустить событие о том, что добавилась новая новость или на сервер загрузился новый файл. В будущем можно написать обработку этих событий, например при добавлении новости можно очистить кэш блока последних новостей, а при загрузки изображения наложить на него водяной знак.
4) Логгировать/регировать. Допустим вы делаете систему документооборота и вам нужно отправить оповещение, если пользователь прочитал документ.
5) Ограничивать доступ. Если пользователь не авторизирован, то показать ему форму авторизации.
6) И многое другое.

# Установка

```composer require arhone/trigger```

```php
<?php
use arhone\trigger\Trigger;

include 'vendor/autoload.php';

$Trigger = new Trigger();
```

# Примеры

```php
<?php

// Добавляем обработчик
$Trigger->add('событие', function () {
    return 'ответ';
});

// Запускаем событие
echo $Trigger->run('событие'); // ответ
```

##### Триггер понимает регулярные выражения

В обработчик приходят три аргумента
1) $match - Массив с совпадениями
2) $data - Данные, переданные вторым параметром в метод run
3) $option - Массив с настройками

```php
<?php

// Добавляем обработчик
$Trigger->add('switch:(on|off)', function ($match, $data, $option) {

    if ($match[1] == 'on') {
        return $data . ' - Включен';
    } else {
        return $data . ' - Отключен';
    }

});

// Запускаем событие
echo $Trigger->run('switch:on', 'Чайник'); // Чайник - Включен
```

##### Регистрация нескольких обработчиков

Ответ предыдущего обработчика, будет передан следующему в $data

```php
<?php

// Добавляем обработчик
$Trigger->add('switch:(on|off)', function ($match, $data) {
    return 'Самовар';
});

// Добавляем обработчик
$Trigger->add('switch:(on|off)', function ($match, $data) {

    if ($match[1] == 'on') {
        return $data . ' - Включен';
    } else {
        return $data . ' - Отключен';
    }

});

// Запускаем событие
echo $Trigger->run('switch:on', 'Чайник'); // Самовар - Включен
```

##### Обработчик можно сделать "обрывающим"

На обрывающем обработчике прервётся стек обработки текущего события, если обработчик вернул не null.

Для создания обрывающего обработчика, нужно установить (break) параметр в true

```php
<?php

// Добавляем обрывающий обработчик
$Trigger->add('switch:(on|off)', function ($match, $data) {

    $energy = false;
    if (!$energy) {
        return 'Нет электричества';
    }

}, [
    'break' => true
]);

// Добавляем обработчик
$Trigger->add('switch:(on|off)', function ($match, $data) {

    if ($match[1] == 'on') {
        return $data . ' - Включен';
    } else {
        return $data . ' - Отключен';
    }

});

// Запускаем событие
echo $Trigger->run('switch:on', 'Чайник'); // Нет электричества
```

##### Позиция в очереди
С  помощью массива $option можно указать порядок запуска обработчиков.

Рекомендуемые значения от -1 до 1 (по умолчанию 0)

Таким образом с помощью позиции -1 можно ставить выполнение обработчика в самое начало, а с помощью 1, в самый конец.

Используйте метод $Trigger->plan() вместо $Trigger->run() что бы увидеть в какой последовательности будут запущены обработчики события.

```php
<?php

$Trigger->add('hello', function ($match, $data) {
    return $data . ' дорогой';
}, [
    'name'     => 'Второй обработчик',
    'position' => 0.2
]);

$Trigger->add('hello', function ($match, $data) {
    return $data . ' мой';
}, [
    'name'     => 'Первый обработчик',
    'position' => 0.1
]);

$Trigger->add('hello', function ($match, $data) {
    return $data . ' друг';
}, [
    'name'     => 'Третий обработчик',
    'position' => 0.3
]);

echo $Trigger->run('hello', 'Привет'); // Привет мой дорогой друг

print_r($Trigger->plan('hello'));
```

##### Именованный обработчики

Обработчику можно задать уникальное имя.

Как видно из примера выше, имя помогает опознать обработчик при использовании метода "plan".

Так же по имени можно переопределить опции другого обработчика.

```php
<?php

$Trigger->add('test', function ($match, $data) use ($Trigger) {

    $Trigger->option('two', [
        'status' => false // Второй обработчик не будет запущен
    ]);

    return 'Первый';

}, [
    'name' => 'one',
]);

$Trigger->add('test', function ($match, $data) {
    return 'Второй';
}, [
    'name' => 'two',
]);

echo $Trigger->run('test'); // Первый
```

##### Включение \ отключение обработчиков

Опция "status" позволяет отключать ненужные обработчики.

```php
<?php

$Trigger->add('start', function ($match, $data) {
    return $data . ' раз';
});

$Trigger->add('start', function ($match, $data) {
    return $data . ' два';
});

$Trigger->add('start', function ($match, $data) {
    return $data . ' три';
}, [
    'status' => false
]);

echo $Trigger->run('start'); // раз два
```

# Ещё примеры

#### Управления маршрутами (Router)

Триггер можно использовать для обработки маршрутов.

Можете сами формировать шаблон запроса, например для запросов через веб сервер, можно указывать HTTP:TYPE:path,
а для запросов через консоль console:command

```php
<?php

$Trigger->add('HTTP:GET:/home.html', function () {
    return 'hello word'; 
});

// Пользователь зашёл по HTTP типа GET на страницу /home.html
echo $Trigger->run('HTTP:GET:/home.html');
```

```php
<?php

$Trigger->add('console:cache-clear', function () {
    return 'Кэш очищен';
});

echo $Trigger->run('console:cache-clear');
```

#### Внедрение посредников (Middleware)

```php
<?php

// Проверяем если пользователь не авторизован
$Trigger->add('HTTP:GET:/home.html', function () {
    if (User::id() == false) {
        return 'Нужно авторизироваться';
    }
}, [
    'break' => true
]);

// Или выводим приветствие
$Trigger->add('HTTP:GET:/home.html', function () {
    return 'Привет'; 
});

// Пользователь зашёл по HTTP типа GET на страницу /home.html
echo $Trigger->run('HTTP:GET:/home.html');
``` 

#### Реагирование на события (Observer)

```php
<?php

// Очищаем кэш новостей
$Trigger->add('module.news.add', function () {
    Cache::clear('module.news');
});

// Событие что была добавлена новость с id 100
$Trigger->run('module.news.add', [
    'id' => 100
]);
``` 
 
#### Обработка запросов.

```php
<?php

// Пишем кэш в Redis
$Trigger->add('cache:set', function ($match, $data) {
    CacheRedis::set($data['key'], $data['value']);
});

// Пишем в файл на всякий случай
$Trigger->add('cache:set', function ($match, $data) {
    CacheFile::set($data['key'], $data['value']);
});

// Генерируем команду на запись в кэш
$Trigger->run('cache:set', [
    'key'   => 'ключ',
    'value' => 'данные для кэширования'
]);
``` 

```php
<?php

// Берём кэш из редиса, если сервер редиса доступен
$Trigger->add('cache:get', function ($match, $data) {
    if (CacheRedis::status() == true) {
        return CacheRedis::get($data['key']);    
    }
}, [
    'break' => true
]); // Параметр break, остановит стек, если обработчик что-то вернул (не null)

// Если редис ничего не вернул, то запустится следующий обработчик и вернёт кэш из файла
$Trigger->add('cache:get', function ($match, $data) {
   if (CacheFile::status() == true) {
       return CacheFile::get($data['key']);
   }
});

// Генерируем команду на получение кэша
$Trigger->run('cache:get', [
    'key' => 'ключ'
]);
``` 

#### Обработка данных стеком обработчиков.
```php
<?php

$Trigger->add('hello', function ($match, $data) {
    return $data . ' мой';
});

$Trigger->add('hello', function ($match, $data) {
    return $data . ' дорогой';
});

$Trigger->add('hello', function ($match, $data) {
    return $data . ' друг';
});

echo $Trigger->run('hello', 'Привет'); // Привет мой дорогой друг
``` 