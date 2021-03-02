<?php

Kirby::plugin('sylvainjule/categories', array(
    'options' => [
        'watch' => []
    ],
    'fields' => [
        'categories' => [
            'props'    => [
                'default' => function($default = false) {
                    return $default;
                },
                'empty' => function($empty = null) {
                    return $empty ?? t('categories.empty');
                },
                'limit' => function($limit = 10) {
                    return $limit;
                },
                'defaultFirst' => function($defaultFirst = false) {
                    return $defaultFirst;
                },
                'prefix' => function($prefix = 'category-') {
                    return $prefix;
                },
                'value' => function ($value = null) {
                    return Yaml::decode($value);
                },
            ],
        ],
    ],
    'fieldMethods'  => [
        'toCategories' => function($field) {
            $categories = count($field->yaml()) ? $field->yaml() : [];
            return new Structure($categories);
        },
        'toCategory' => function($field, $list, $lang = false, $delim = ',') {
            $list = $list->toCategories();
            $categories = [];

            foreach($field->split($delim) as $c) {
                $category   = $list->findBy('id', $c);

                if(!$category) $categories[] = null;

                if($lang && array_key_exists($lang, $category->translations()->value())) {
                    $categories[] = $category->translations()->value()[$lang];
                }
                else {
                    $categories[] = $category->text()->value();
                }
            }

            return count($categories) == 1 ? $categories[0] : $categories;
        },
    ],
    'pageMethods' => [
        'updateCategoriesStructure' => function($fieldname) {
            $fieldContent = $this->$fieldname()->yaml();
            if(count($fieldContent) == 2 && is_int($fieldContent[1])) {
                $newFieldContent = $fieldContent[0];
                foreach(kirby()->languages() as $l) {
                    $this->save([$fieldname => $newFieldContent], $l->code());
                }
            }
        },
    ],
    'hooks' => [
        'page.update:after' => function($newPage, $oldPage) {
            if(!option('languages')) return false;

            $watch       = option('sylvainjule.categories.watch');
            $templates   = array_keys($watch);
            $template    = $newPage->intendedTemplate()->name();

            if(in_array($template, $templates)) {
                $fieldnames = $watch[$template];
                $fieldnames = is_string($fieldnames) ? [$fieldnames] : $fieldnames;
                $kirby      = kirby();
                $languages  = $kirby->languages()->not($kirby->language());

                foreach($languages as $l) {
                    $update = [];

                    foreach($fieldnames as $fieldname) {
                        if($newPage->$fieldname() !== $oldPage->$fieldname()) {
                            $categories = $newPage->$fieldname()->yaml();

                            $categories = array_map(function($category) use($l) {
                                $translations = $category['translations'];
                                $text         = array_key_exists($l->code(), $translations) ? $translations[$l->code()] : '';
                                $category['text'] = $text;
                                return $category;
                            }, $categories);

                            $update[$fieldname] = $categories;
                        }
                    }
                    if(!empty($update)) {
                        $newPage->save($update, $l->code());
                    }
                }
            }
        }
    ],
    'translations' => [
        'en' => [
            'categories.empty' => 'No categories yet'
        ],
        'fr' => [
            'categories.empty' => 'Pas encore de catégorie'
        ],
    ],
));
