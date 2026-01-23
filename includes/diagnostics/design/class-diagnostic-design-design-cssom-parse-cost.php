<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSSOM Parse Cost Estimate
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-cssom-parse-cost
 * Training: https://wpshadow.com/training/design-cssom-parse-cost
 */
class Diagnostic_Design_DESIGN_CSSOM_PARSE_COST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-cssom-parse-cost',
            'title' => __('CSSOM Parse Cost Estimate', 'wpshadow'),
            'description' => __('Estimates CSSOM parse cost from rules, selectors, and specificity.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-cssom-parse-cost',
            'training_link' => 'https://wpshadow.com/training/design-cssom-parse-cost',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}