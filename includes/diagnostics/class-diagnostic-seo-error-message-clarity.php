<?php declare(strict_types=1);
/**
 * Error Message Clarity Diagnostic
 *
 * Philosophy: Clear errors reduce frustration
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Error_Message_Clarity {
    public static function check() {
        return [
            'id' => 'seo-error-message-clarity',
            'title' => 'Error Message User-Friendliness',
            'description' => 'Use clear, actionable error messages. Avoid technical jargon, provide solutions.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/error-messages/',
            'training_link' => 'https://wpshadow.com/training/ux-writing/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
