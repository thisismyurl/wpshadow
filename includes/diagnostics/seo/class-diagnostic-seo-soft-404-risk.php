<?php
declare(strict_types=1);
/**
 * Soft 404 Risk Diagnostic
 *
 * Philosophy: Ensure proper 404 handling
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Soft_404_Risk extends Diagnostic_Base {
    /**
     * Check if theme lacks a 404 template, increasing soft 404 risks.
     *
     * @return array|null
     */
    public static function check(): ?array {
        $template = locate_template('404.php', false, false);
        if (empty($template)) {
            return [
                'id' => 'seo-soft-404-risk',
                'title' => 'Theme Missing 404 Template',
                'description' => 'No 404.php template found in the active theme. This can lead to soft 404s (pages returning 200 with “not found” content).',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/proper-404-handling/',
                'training_link' => 'https://wpshadow.com/training/http-status-seo/',
                'auto_fixable' => false,
                'threat_level' => 45,
            ];
        }
        return null;
    }
}
