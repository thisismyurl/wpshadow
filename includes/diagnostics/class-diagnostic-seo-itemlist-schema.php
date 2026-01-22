<?php declare(strict_types=1);
/**
 * ItemList Schema Diagnostic
 *
 * Philosophy: ItemList for lists and rankings
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_ItemList_Schema {
    public static function check() {
        return [
            'id' => 'seo-itemlist-schema',
            'title' => 'ItemList Schema for Rankings',
            'description' => 'Add ItemList schema for top 10 lists, rankings, or carousels.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/itemlist-schema/',
            'training_link' => 'https://wpshadow.com/training/list-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
