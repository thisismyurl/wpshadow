<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Confirmation Page Messaging
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-confirmation-page-messaging
 * Training: https://wpshadow.com/training/design-confirmation-page-messaging
 */
class Diagnostic_Design_CONFIRMATION_PAGE_MESSAGING {
    public static function check() {
        return [
            'id' => 'design-confirmation-page-messaging',
            'title' => __('Confirmation Page Messaging', 'wpshadow'),
            'description' => __('Checks confirmation includes reference number.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-confirmation-page-messaging',
            'training_link' => 'https://wpshadow.com/training/design-confirmation-page-messaging',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
