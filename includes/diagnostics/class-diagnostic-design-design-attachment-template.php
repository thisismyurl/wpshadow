<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Attachment Template
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-attachment-template
 * Training: https://wpshadow.com/training/design-attachment-template
 */
class Diagnostic_Design_DESIGN_ATTACHMENT_TEMPLATE {
    public static function check() {
        return [
            'id' => 'design-attachment-template',
            'title' => __('Attachment Template', 'wpshadow'),
            'description' => __('Checks attachments are styled and navigable.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-attachment-template',
            'training_link' => 'https://wpshadow.com/training/design-attachment-template',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

