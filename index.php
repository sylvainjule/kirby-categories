<?php

require_once __DIR__ . '/lib/categories.php';

Kirby::plugin('sylvainjule/categories', array(
    'options' => [
        'watch' => []
    ],
    'hooks' => [
        'page.update:after' => function($newPage, $oldPage) {
            if(!option('languages')) return;

            $categories = new Categories();
            $changes    = $categories->getChangesArray($this, $newPage, $oldPage);

            if(count($changes)) {
                $newPage = $categories->saveChanges($this, $newPage, $changes);
            }

            return $newPage;
        },
        'site.update:after' => function($newSite, $oldSite) {
            if(!option('languages')) return;

            $categories = new Categories();
            $changes    = $categories->getChangesArray($this, $newSite, $oldSite);

            if(count($changes)) {
                $newSite = $categories->saveChanges($this, $newSite, $changes);
            }

            return $newSite;
        }
    ],
));
