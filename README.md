# LiteMemcache ![English version](http://upload.wikimedia.org/wikipedia/en/thumb/a/ae/Flag_of_the_United_Kingdom.svg/22px-Flag_of_the_United_Kingdom.svg.png)

*LiteMemcache is the most lightweight Memcached client written in PHP*

**Key points**

* *Full-featured:* supports all necessary Memcached commands
* *Simple:* just Memcached protocol, nothing more
* *Really tiny:* only 110 lines of code
* *Requires nothing:* pure PHP implementation

**Usage example**

```php
$client = new LiteMemcache( 'host:port' );
$client->set( 'key', 'value' );
$value = $client->get( 'key' );
```

--------------------------------------------------

# LiteMemcache ![Русская версия](http://upload.wikimedia.org/wikipedia/en/thumb/f/f3/Flag_of_Russia.svg/22px-Flag_of_Russia.svg.png)

*LiteMemcache - самый легковесный клиент для Memcached, написанный на PHP*

**Основные моменты**

* *Полнофункциональный:* поддерживает все команды Memcached
* *Простой:* только протокол Memcached, ничего лишнего
* *Крошечный:* всего 110 строк кода
* *Нетребовательный:* написан на чистом PHP

**Пример использования**

```php
$client = new LiteMemcache( 'хост:порт' );
$client->set( 'ключ', 'значение' );
$value = $client->get( 'ключ' );
```

--------------------------------------------------

Keywords: litememcache, memcached, memcache, storage, nosql, php, client, lightweight, light, easy, simple, small, tiny, protocol