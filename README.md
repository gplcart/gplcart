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

Step 1. Download and unpack to your installation directory. It can be done manually or using Composer: `composer create-project gplcart/gplcart path/to/installation/folder`. You may want to use some optional parameters like: `--no-install --`. See [composer docs](https://getcomposer.org/doc).

Step 2. Perform full system installation using one of the following options:

1. **Web installer:** Go to `yourdomain.com/install` and follow the instructions
2. **Console:** Go to your installation directory `cd /your/installation/directory`, then `php gplcart install --db-name="example" --user-email="example@example.com"`. To see all available install options: `php gplcart install --help`


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
- Supports both PHP and TWIG templates (you can even mix them!)

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
- Built-in template/css/js editor
- Backups

...and much more!
