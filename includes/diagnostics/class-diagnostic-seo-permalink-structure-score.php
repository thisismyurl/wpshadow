<?php declare(strict_types=1);
/**
 * Permalink Structure Score Diagnostic
 *
 * Philosophy: SEO-friendly URL patterns
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Permalink_Structure_Score {
    public static function check() {
        $structure = get_option('permalink_structure');
        if (empty($structure) || $structure === '/?p=%post_id%') {
            return [
                'id' => 'seo-permalink-structure-score',
                'title' => 'Non-SEO-Friendly Permalinks',
                'description' => 'Permalink structure is not SEO-friendly. Use /%postname%/ or /%category%/%postname%/ for better URLs.',
                'severity' => 'high',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/permalink-structure/',
                'training_link' => 'https://wpshadow.com/training/wordpress-seo-basics/',
                'auto_fixable' => false,
                'threat_level' => 75,
            ];
        }
        return null;
    }
}
