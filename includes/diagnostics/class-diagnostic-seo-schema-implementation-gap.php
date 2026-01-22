<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Schema_Implementation_Gap {
    public static function check() {
        return ['id' => 'seo-schema-gap', 'title' => __('Schema Implementation Gap vs Competitors', 'wpshadow'), 'description' => __('Analyzes which schema types competitors implement that you don\'t. Missing Product, Review, or Article schema when competitors have it = lost visibility.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/schema-types/', 'training_link' => 'https://wpshadow.com/training/structured-data/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
