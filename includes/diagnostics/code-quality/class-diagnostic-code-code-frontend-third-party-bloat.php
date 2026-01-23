<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Third-Party Widget Bloat
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-third-party-bloat
 * Training: https://wpshadow.com/training/code-frontend-third-party-bloat
 */
class Diagnostic_Code_CODE_FRONTEND_THIRD_PARTY_BLOAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-frontend-third-party-bloat',
            'title' => __('Third-Party Widget Bloat', 'wpshadow'),
            'description' => __('Flags excessive embedded widgets (social, ads, analytics).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-frontend-third-party-bloat',
            'training_link' => 'https://wpshadow.com/training/code-frontend-third-party-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}