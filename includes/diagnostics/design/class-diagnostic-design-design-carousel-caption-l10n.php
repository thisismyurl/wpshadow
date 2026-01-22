<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Carousel Caption Localization
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-carousel-caption-l10n
 * Training: https://wpshadow.com/training/design-carousel-caption-l10n
 */
class Diagnostic_Design_DESIGN_CAROUSEL_CAPTION_L10N extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-carousel-caption-l10n',
            'title' => __('Carousel Caption Localization', 'wpshadow'),
            'description' => __('Checks captions wrap without overlap in carousels.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-carousel-caption-l10n',
            'training_link' => 'https://wpshadow.com/training/design-carousel-caption-l10n',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
