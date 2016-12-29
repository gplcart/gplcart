# Transliterator
A standalone PHP class that provides one-way string transliteration (romanization). Supports a lot of languages out-of-box and automatically defines the source language.

This is just a wrapper class for [Drupal's Transliteration](https://www.drupal.org/project/transliteration) module, all credits go to the original authors:

- Stefan M. Kudwien (smk-ka)
- Daniel F. Kudwien (sun)

Usage:

$translit = Transliterator::transliterate('中國');
// Result: Zhong Guo

Check in [Google Translate](https://translate.google.com/?ie=UTF-8&hl=en&client=tw-ob#zh-CN/en/%E4%B8%AD%E5%9C%8B). Pretty cool, huh?
