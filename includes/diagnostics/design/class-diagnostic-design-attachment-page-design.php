<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Attachment Page Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-attachment-page-design
 * Training: https://wpshadow.com/training/design-attachment-page-design
 */
class Diagnostic_Design_ATTACHMENT_PAGE_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-attachment-page-design',
            'title' => __('Attachment Page Design', 'wpshadow'),
            'description' => __('Validates media attachment pages styled.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-attachment-page-design',
            'training_link' => 'https://wpshadow.com/training/design-attachment-page-design',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}