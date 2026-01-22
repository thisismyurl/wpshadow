<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Focus Order Logical
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-focus-order-logical
 * Training: https://wpshadow.com/training/design-focus-order-logical
 */
class Diagnostic_Design_FOCUS_ORDER_LOGICAL extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-focus-order-logical',
            'title' => __('Focus Order Logical', 'wpshadow'),
            'description' => __('Validates tab order follows logical reading order.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-focus-order-logical',
            'training_link' => 'https://wpshadow.com/training/design-focus-order-logical',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
