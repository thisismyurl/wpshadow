<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Tablet Layout Optimization
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-tablet-layout-optimization
 * Training: https://wpshadow.com/training/design-tablet-layout-optimization
 */
class Diagnostic_Design_TABLET_LAYOUT_OPTIMIZATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-tablet-layout-optimization',
            'title' => __('Tablet Layout Optimization', 'wpshadow'),
            'description' => __('Validates layout designed for tablet (768px-1024px).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-tablet-layout-optimization',
            'training_link' => 'https://wpshadow.com/training/design-tablet-layout-optimization',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}