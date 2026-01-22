<?php declare(strict_types=1);
/**
 * Contact Information Prominence Diagnostic
 *
 * Philosophy: Visible contact info builds trust
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Contact_Information_Prominence {
    public static function check() {
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
