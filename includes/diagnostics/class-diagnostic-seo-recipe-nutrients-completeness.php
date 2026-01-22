<?php declare(strict_types=1);
/**
 * Recipe Nutrients Completeness Diagnostic
 *
 * Philosophy: Complete recipe data for rich results
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Recipe_Nutrients_Completeness {
    public static function check() {
        return [
            'id' => 'seo-recipe-nutrients-completeness',
            'title' => 'Recipe Nutrients & Times Completeness',
            'description' => 'Ensure Recipe schema includes nutrition info, prep time, and cook time where applicable.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/recipe-schema-completeness/',
            'training_link' => 'https://wpshadow.com/training/schema-serp-features/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
