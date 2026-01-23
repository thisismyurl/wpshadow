<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Infinite Scroll Considerations
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-infinite-scroll-considerations
 * Training: https://wpshadow.com/training/design-infinite-scroll-considerations
 */
class Diagnostic_Design_INFINITE_SCROLL_CONSIDERATIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-infinite-scroll-considerations',
            'title' => __('Infinite Scroll Considerations', 'wpshadow'),
            'description' => __('Checks infinite scroll has endpoint message.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-infinite-scroll-considerations',
            'training_link' => 'https://wpshadow.com/training/design-infinite-scroll-considerations',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}