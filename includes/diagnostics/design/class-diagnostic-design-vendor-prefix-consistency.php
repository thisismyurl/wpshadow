<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Vendor Prefix Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-vendor-prefix-consistency
 * Training: https://wpshadow.com/training/design-vendor-prefix-consistency
 */
class Diagnostic_Design_VENDOR_PREFIX_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vendor-prefix-consistency',
            'title' => __('Vendor Prefix Consistency', 'wpshadow'),
            'description' => __('Checks CSS vendor prefixes consistent.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vendor-prefix-consistency',
            'training_link' => 'https://wpshadow.com/training/design-vendor-prefix-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}