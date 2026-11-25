# Kirby Translated Categories

This plugin helps dealing with translated categories, adding UUIDs to structure fields and the ability to sync their content accross all languages.

![Image](https://github.com/user-attachments/assets/18f1a92c-1cfe-4706-b40b-37a6a14e4df0)

<br/>

## Overview

> This plugin is completely free and published under the MIT license. However, if you are using it in a commercial project and want to help me keep up with maintenance, you can consider [making a donation of your choice](https://www.paypal.me/sylvainjl).

- [1. Installation](#1-installation)
- [2. Panel setup](#2-panel-setup)
- [3. Options](#3-options)
- [4. Frontend usage](#4-frontend-usage)
- [5. Compatibility with other hooks](#5-compatibility-with-other-hooks)
- [6. License](#6-license)

<br/>

## 1. Installation

> Version **2.0.0** introduces breaking changes from previous 1.x.x versions (explained and documented in the release). Versions **2.x** are compatible with Kirby 5 and Kirby 6.

Download and copy this repository to ```/site/plugins/categories```

Alternatively, you can install it with composer: ```composer require sylvainjule/categories```

<br/>

## 2. Panel setup

This plugin allows an editor to easily create and manage multi-language categories, while keeping the field's content synced between languages in order to keep IDs unique. **It will only work in multi-language setups.**

Whenever a user adds a new item to a synced structure field, all languages inherit the new item.<br/>
Whenever a user deletes an item from a synced structure field, it is deleted from all languages.<br/>
Whenever a user sorts a synced structure field, the new sort order is applied in all languages.

It requires two steps:

First, add a structure field anywhere in your blueprints:

```yaml
fields:
  categories:
    label: Categories
    type: structure
```

Second, you need to tell the plugin which `template => fieldname` to watch and sync accross languages, by adding this option to your config file

```php
// site/config/config.php
'sylvainjule.categories.watch' => [
    'template' => 'fieldname',
    'template' => ['fieldname1', 'fieldname2'],
    'site'     => 'fieldname', # can also be used with the site.yml blueprint
]
```

For example, if you have a `blog` template with a `categories` field, and a `projects` template with `clients` + `techniques` fields, you will need to set:

```php
// site/config/config.php
'sylvainjule.categories.watch' => [
    'blog'     => 'categories',
    'projects' => ['clients', 'techniques'],
]
```

You can then set the categories created with the field as dynamic options of `select`, `multiselect`, `checkboxes`, etc. Refer to [the official documentation](https://getkirby.com/docs/reference/panel/fields/multiselect#options-from-other-fields__options-from-structure-field) about using structure field items as options.

Since the plugin makes sure that a given category shares the same UUID accross all languages, use `{{ structureItem.uuid }}` as an immutable value. The `text` property can be any field from your structure.

```yaml
category:
  label: Category
  type: select
  # kirby 6 ↓
  options:
    type: query
    query: page.parent.categories.toStructure
    text: "{{ item.name }}"
    value: "{{ item.uuid }}"
  # kirby 5 ↓
  options: query
  query:
    fetch: page.parent.categories.toStructure
    text: "{{ structureItem.fieldname }}"
    value: "{{ structureItem.uuid }}"
```

<br>


## 3. Options

### 3.1. Watch

An array of `template => [fields]` pairs to watch (default is `empty | []`). See above for examples. 

```php
'sylvainjule.categories.watch' => []
```

### 3.2. Hook

Setting this option to `false` (default is `true`) will prevent the plugin to apply any change in the `page.update:after` | `site.update:after` hooks (you would then have to manually call it, see [5. Compatibility with other hooks](#5-compatibility-with-other-hooks)).

```php
'sylvainjule.categories.hook' => true,
```

<br>

## 4. Frontend usage

You can use the field like any other Structure, but knowing your categories share an immutable UUID accross languages. Few examples:

```php
// select field
$categories = $page->parent()->categories()->toStructure(); // our structure field
$category   = $page->category(); // a select field value referencing structureItem.uuid
$category   = $categories->findBy('uuid', $category); // we convert this UUID into the associated Structure Object

// multiselect field
$categories         = $page->parent()->categories()->toStructure(); // our structure field
$selectedArray      = $page->categories()->split(); // a multiselect field value referencing structureItem.uuid
$selectedCategories = $categories->filter(function($category) use($selectedArray) {
  return in_array($category->uuid()->value(), $selectedArray);
}); // we convert this array of UUIDs into a filtered Structure

...
```


<br/>

## 5. Compatibility with other hooks

The plugin uses the `site.update:after` and `page.update:after` hooks to synchronize structure fields across languages.

If your project or another plugin registers additional hooks that modify the same model, it is recommended to always return the updated instance:

```php
'page.update:after' => function($newPage, $oldPage) {
    return $newPage->save([
        'somefield' => 'somevalue'
    ]);
}
```

If you run into issues or for integrations where you need full control over the synchronization process or the order of updates, you can disable the plugin's built-in hooks:

```php
'sylvainjule.categories.hook' => false,
```

And call its synchronization methods manually in yours:

```php
'page.update:after' => function($newPage, $oldPage) {
    $categories = new Categories();
    $changes    = $categories->getChangesArray($this, $newPage, $oldPage);

    if(count($changes)) {
        $newPage = $categories->saveChanges($this, $newPage, $changes);
    }

    return $newPage;
}

```


<br/>

## 6. License

MIT

