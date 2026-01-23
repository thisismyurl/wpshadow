<?php
declare(strict_types=1);
/**
 * AMP Validity Status Diagnostic
 *
 * Philosophy: Invalid AMP hurts mobile ranking
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_AMP_Validity_Status extends Diagnostic_Base {
    public static function check(): ?array {
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