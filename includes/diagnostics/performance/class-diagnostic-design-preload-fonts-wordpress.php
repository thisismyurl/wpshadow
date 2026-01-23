<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WordPress Font Preloading
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-preload-fonts-wordpress
 * Training: https://wpshadow.com/training/design-preload-fonts-wordpress
 */
class Diagnostic_Design_PRELOAD_FONTS_WORDPRESS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-preload-fonts-wordpress',
            'title' => __('WordPress Font Preloading', 'wpshadow'),
            'description' => __('Validates Google Fonts preloaded in wp_head().', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-preload-fonts-wordpress',
            'training_link' => 'https://wpshadow.com/training/design-preload-fonts-wordpress',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}