[![Build Status](https://scrutinizer-ci.com/g/gplcart/gplcart/badges/build.png?b=dev)](https://scrutinizer-ci.com/g/gplcart/gplcart/build-status/dev)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/gplcart/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/gplcart/gplcart/?branch=dev)

## WARNING. Dev branch in not for production. Please wait until the release of 1.X ##

## About ##
GPLCart is an open source e-commerce platform based on the classical LAMP stack (Linux+ Apache+Mysql+PHP). It is free, simple, modern and extendable solution that allows you to build online-shops fast and easy.

## Requirements ##

- PHP 5.4+, Mysql 5+, Apache 1+

Also you'll need the following extension enabled:

- Curl
- OpenSSL
- Mb string
- Mod Rewrite
- ZipArchive

## Installation ##

Download and unpack to your hosting directory. Go to `yourdomain.com/install` and follow the instructions. That's all!

## Some key features ##

For developers:

- Simple MVC pattern
- PSR-0, PSR-4 standard compliance
- Dependency injection
- Minimum static methods
- Modules are damn simple, theme = module
- Tons of hooks
- Command line support (extensible)
- Ability to rewrite almost any core method from a module (no monkey patching, "VQ mods")
- Supports both PHP and TWIG templates

For owners:

- Multistore `anotherstore.com, anotherstore.domain.com, domain.com/anotherstore`
- No stupid cart pages. Yes, it's a feature. Customer goes immediately to checkout
- True one page checkout that works even with JS turned off
- Product classes
- Product fields (images, colors, text)
- Product combinations (XL + red, XL + green etc)
- Powerful export/import for everything (*100.000* products? No problem!)
- Super flexible price rules both for catalog and checkout (including coupons)
- Integrated with Google Analytics
- Roles and access control

...and much more!

----------
### <a name="ru"></a>

## О программе ##
GPLCart - это движок интернет-магазина с открытым исходным кодом, который работает на классической связке PHP+Mysql. Это бесплатное, современное и расширяемое решение, которое позволяет строить интернет-магазины быстро и просто!

## Системные требования ##

- PHP 5.4+, Mysql 5+, Apache 1+

Также ваш PHP должен иметь следущие расширения:

- Curl
- OpenSSL
- Mb string
- Mod Rewrite
- ZipArchive

## Установка ##
Скачайте архив и установите в рабочую директорию вашего хостинга. Наберите `yourdomain.com` и следуйте инструкциям. Это всё!

## Некоторые возможности системы ##

Для разработчиков:

- Простой MVC паттерн
- Соответствие стандартам PSR-0, PSR-4
- Инъекции зависимостей, контейнер
- Минимум статических методов
- Простейшая архитектура модулей, темы тоже модули
- Тонны хуков для отслеживания/изменения поведения системы из модулей
- Поддержка коммандной строки (расширяемо)
- Возможность перезаписи методов ядра без хаков и "модов"
- Поддерживает классические PHP шаблоны и TWIG

Для владельцев:

- Мультисайтинг вида `anotherstore.com, anotherstore.domain.com, domain.com/anotherstore`
- Нет корзины. Это фича. Всё на странице чекаута.
- Настоящий одностраничный чекаут с AJAX перегрузкой. Работает и без JS кстати
- Классы товаров
- Поля товаров (текст, картинки, цвета)
- Комбинации опций (XL + красный, XL + зелёный и тд). Самое лёгкое управление ними, которое вы когда либо видели
- Мощный экспорт/импорт из CSV. Как насчёт 100.000 товаров на слабеньком виртуальном хостинге? Без проблем!
- Гибкие правила цен для каталога и чекаута (купоны)
- Встроенная поддержка Google Analytics
- Мощная система контроля доступа, роли

...и много чего ещё!

