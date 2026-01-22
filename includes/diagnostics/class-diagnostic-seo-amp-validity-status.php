<?php declare(strict_types=1);
/**
 * AMP Validity Status Diagnostic
 *
 * Philosophy: Invalid AMP hurts mobile ranking
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_AMP_Validity_Status {
    public static function check() {
        return [
            'id' => 'seo-amp-validity-status',
            'title' => 'AMP Validation Status',
            'description' => 'If using AMP, validate all AMP pages with official validator. Invalid AMP pages may not be indexed.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/amp-validation/',
            'training_link' => 'https://wpshadow.com/training/amp-implementation/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
