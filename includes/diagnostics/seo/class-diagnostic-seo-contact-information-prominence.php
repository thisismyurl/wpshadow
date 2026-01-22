<?php
declare(strict_types=1);
/**
 * Contact Information Prominence Diagnostic
 *
 * Philosophy: Visible contact info builds trust
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Contact_Information_Prominence extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-contact-information-prominence',
            'title' => 'Contact Information Visibility',
            'description' => 'Display contact information prominently. Address, phone, email establish legitimacy.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/contact-info/',
            'training_link' => 'https://wpshadow.com/training/business-transparency/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
