<?php

class Translate {
    public static function postProcessTranslation($translation) {
        return $translation;
    }

    public static function getModuleTranslation(
        $module,
        $originalString,
        $source,
        $sprintf = null,
        $js = false,
        $locale = null,
        $fallback = true
    ) {
        return $originalString;
    }
}