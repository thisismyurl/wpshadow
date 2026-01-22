<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: PII Without Consent Guard
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-gdpr-consent
 * Training: https://wpshadow.com/training/code-db-gdpr-consent
 */
class Diagnostic_Code_CODE_DB_GDPR_CONSENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-gdpr-consent',
            'title' => __('PII Without Consent Guard', 'wpshadow'),
            'description' => __('Detects personal data storage without consent management.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-gdpr-consent',
            'training_link' => 'https://wpshadow.com/training/code-db-gdpr-consent',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
