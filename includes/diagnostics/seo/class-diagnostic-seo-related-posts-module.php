<?php
declare(strict_types=1);
/**
 * Related Posts Module Diagnostic
 *
 * Philosophy: Improve internal linking automatically
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Related_Posts_Module extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-related-posts-module',
            'title' => 'Related Posts Module',
            'description' => 'Add a related posts module to improve internal linking and keep users engaged.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/related-posts/',
            'training_link' => 'https://wpshadow.com/training/internal-linking/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }

}