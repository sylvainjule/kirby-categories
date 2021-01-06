# Kirby Translated Categories

This plugin helps dealing with translated categories, providing a field with cross-languages sync + unique ids, and a few field methods.

![screenshot](https://user-images.githubusercontent.com/14079751/79684385-2bea3880-8231-11ea-8889-a2846b196070.png)

<br/>

## Overview

> This plugin is completely free and published under the MIT license. However, if you are using it in a commercial project and want to help me keep up with maintenance, please consider [making a donation of your choice](https://www.paypal.me/sylvainjl) or purchasing your license(s) through [my affiliate link](https://a.paddle.com/v2/click/1129/36369?link=1170).

- [1. Installation](#1-installation)
- [2. Panel setup](#2-panel-setup)
- [3. Options](#3-options)
- [4. Frontend usage](#4-frontend-usage)
- [5. Alternatives](#5-alternatives)
- [6. License](#6-license)

<br/>

## 1. Installation

Download and copy this repository to ```/site/plugins/categories```

Alternatively, you can install it with composer: ```composer require sylvainjule/categories```

<br/>

## 2. Panel setup

The intent of the `categories` field is to allow an editor to easily create and manage multi-language categories, while keeping the field's content synced between languages in order to keep IDs unique. **It will only work in multi-language setups.**

It requires two steps:

First, add the field anywhere in your blueprints:

```yaml
fields:
  categories:
    label: Categories
    type: categories
```

Second, you need to tell the plugin which `template => fieldname` to watch and sync accross languages, by adding this option to your config file:

```php
// site/config/config.php
return [
    'sylvainjule.categories.watch' => [
        'template' => 'fieldname',
        'template' => ['fieldname1', 'fieldname2'],
    ]
];
```

For example, if you have a `blog` template with a `categories` field, and a `projects` template with `clients` + `techniques` fields, you will need to set:

```php
// site/config/config.php
return [
    'sylvainjule.categories.watch' => [
        'blog'     => 'categories',
        'projects' => ['clients', 'techniques'],
    ]
];
```

You can then set the categories created with the field as dynamic options of `select`, `multiselect`, `checkboxes`, etc.

```yaml
category:
  label: Category
  type: select
  options: query
  query:
    fetch: page.parent.categories.toCategories
    text: "{{ structureItem.text }}"
    value: "{{ structureItem.id }}"
```

<br>

## 3. Options

### 3.1. Prefix

The plugin stores an ID for each list item : `{{prefix}}-{{index}}`. The index is automatically incremented everytime a new category is added, but you can choose the prefix you'd like for each field (default is `category-`):

```yaml
fields:
  countries:
    label: Countries
    type: categories
    prefix: country- # the field will store country-1, country-2, etc.
```

### 3.2. defaultFirst

By default, languages are displayed in alphabetical order. If you want to have the default language appear first, set this option to `true`. Default is `false`.

```yaml
fields:
  countries:
    label: Countries
    type: categories
    defaultFirst: true
```

<br>

## 4. Frontend usage

There are few available methods to make handling categories easier. To get the whole categories list:

```php
// returns a Structure
$categories = $page->categories()->toCategories();

foreach($categories as $category) {
    echo $category->text();
    // ... see below the list of all properties
}
```

From there you have access to a Structure Object with the following properties:

```yaml
# content of a category
id: category-1       # $category->id()
text: 'My category'  # $category->text()
translations:        # $category->translations(), Array
  en: 'My category'  # $category->translations()['en']
  fr: 'Ma catégorie' # $category->translations()['fr']
```

If you have set a `select`, `multiselect`, `checkboxes`, etc. options from a categories field, the field will have stored the ID of the category. To get the text from there:

```php
// toCategory($list, $lang = false)
$list = $page->parent()->categories();
$text = $page->category()->toCategory($list); // returns the category's text in the current language
$text = $page->category()->toCategory($list, 'fr'); // returns the category's text in French

// turns
'category-1' into 'My category' // returns a string
// or
'category-1,category-2' into ['My category', 'My category 2'] // returns an array
```

<br/>

## 5. Alternatives

The plugin aims to solve a very specific use-case: managing single-text categories. If you need to have more data associated with each category, this is not the one.

In this case I'd recommend working with pages + [autoid](https://github.com/bnomei/kirby3-autoid), one page per category where you can associate as much metadata as you'd like. You will need to remove them from your index / searchable pages / … later on.

Let me know if you have other convenient ways to deal with complex multi-language categories, I'll add them here.

<br/>

## 6. License

MIT

