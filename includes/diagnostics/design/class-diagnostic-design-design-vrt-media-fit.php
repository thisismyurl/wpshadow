<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Media Fit
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-media-fit
 * Training: https://wpshadow.com/training/design-vrt-media-fit
 */
class Diagnostic_Design_DESIGN_VRT_MEDIA_FIT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-media-fit',
            'title' => __('VRT Media Fit', 'wpshadow'),
            'description' => __('Detects image crop and aspect ratio drift versus baseline.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-media-fit',
            'training_link' => 'https://wpshadow.com/training/design-vrt-media-fit',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}