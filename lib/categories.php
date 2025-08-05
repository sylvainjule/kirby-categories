<?php 

use Kirby\Uuid\Uuid;

class Categories {

    public static function getChangesArray($kirby, $newPage, $oldPage) {
        $watch       = option('sylvainjule.categories.watch');
        $templates   = array_keys($watch);
        $template    = $newPage instanceof Kirby\Cms\Site ? 'site' : $newPage->intendedTemplate()->name();
        $changes     = [];

        if(in_array($template, $templates)) {
            $fieldnames      = $watch[$template];
            $fieldnames      = is_string($fieldnames) ? [$fieldnames] : $fieldnames;
            $currentLanguage = $kirby->language();
            $currentCode     = $currentLanguage->code();
            $otherLanguages  = $kirby->languages()->not($currentLanguage);

            foreach($fieldnames as $fieldname) {
                $oldData = $oldPage->content($currentCode)->get($fieldname)->yaml();
                $data    = $newPage->content($currentCode)->get($fieldname)->yaml();

                if($data === $oldData || !count($data)) continue;

                $data = array_map(function($item) {
                    if(empty($item['uuid'])) {
                        $item['uuid'] = Uuid::generate();
                    }
                    return $item;
                }, $data);

                $changes[$currentCode][$fieldname] = Data::encode($data, 'yaml');

                foreach($otherLanguages as $lang) {
                    $langCode    = $lang->code();
                    $langData    = $newPage->content($langCode)->get($fieldname)->yaml();
                    $newLangData = [];

                    foreach($data as $item) {
                        $index = array_search($item['uuid'], array_column($langData, 'uuid'));

                        $newLangData[] = $index > -1 ? $langData[$index] : $item;
                    }

                    $changes[$langCode][$fieldname] = Data::encode($newLangData, 'yaml');
                }
            }
        }

        return $changes;
    }

    public function saveChanges($kirby, $newPage, $changes) {
        $currentLanguage = $kirby->language();
        $currentCode     = $currentLanguage->code();
        $otherLanguages  = $kirby->languages()->not($currentLanguage);

        if(array_key_exists($currentCode, $changes)) {
            $newPage = $newPage->save($changes[$currentCode], $currentCode);
        }
        foreach($otherLanguages as $lang) {
            if(array_key_exists($lang->code(), $changes)) {
                $newPage = $newPage->save($changes[$lang->code()], $lang->code());
            }
        }

        return $newPage;
    }
}