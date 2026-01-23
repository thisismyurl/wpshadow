<?php
declare(strict_types=1);
/**
 * Taxonomy Bloat Diagnostic
 *
 * Philosophy: Unused terms dilute crawl budget
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Taxonomy_Bloat extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $unused_terms = (int) $wpdb->get_var("SELECT COUNT(t.term_id) FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.count = 0");
        if ($unused_terms > 50) {
            return [
                'id' => 'seo-taxonomy-bloat',
                'title' => 'Unused Taxonomy Terms',
                'description' => sprintf('%d unused tags/categories detected. Clean up to improve crawl efficiency.', $unused_terms),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/taxonomy-cleanup/',
                'training_link' => 'https://wpshadow.com/training/taxonomy-optimization/',
                'auto_fixable' => false,
                'threat_level' => 20,
            ];
        }
        return null;
    }

}