<?php
declare(strict_types=1);
/**
 * Google Business Integration Diagnostic
 *
 * Philosophy: Link to GMB with UTM tracking
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Google_Business_Integration extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-google-business-integration',
            'title' => 'Google Business Profile Integration',
            'description' => 'Link to Google Business Profile from website with UTM parameters to track traffic and improve local signals.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/google-business-integration/',
            'training_link' => 'https://wpshadow.com/training/local-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
