<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CTA Consistency Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-cta-consistency-score
 * Training: https://wpshadow.com/training/design-cta-consistency-score
 */
class Diagnostic_Design_DESIGN_CTA_CONSISTENCY_SCORE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-cta-consistency-score',
            'title' => __('CTA Consistency Score', 'wpshadow'),
            'description' => __('Scores CTA size, color, and weight consistency across the site.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-cta-consistency-score',
            'training_link' => 'https://wpshadow.com/training/design-cta-consistency-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
