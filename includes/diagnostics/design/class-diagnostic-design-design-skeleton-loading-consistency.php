<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Skeleton Loading Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-skeleton-loading-consistency
 * Training: https://wpshadow.com/training/design-skeleton-loading-consistency
 */
class Diagnostic_Design_DESIGN_SKELETON_LOADING_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-skeleton-loading-consistency',
            'title' => __('Skeleton Loading Consistency', 'wpshadow'),
            'description' => __('Checks skeletons are consistent and avoid CLS.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-skeleton-loading-consistency',
            'training_link' => 'https://wpshadow.com/training/design-skeleton-loading-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}