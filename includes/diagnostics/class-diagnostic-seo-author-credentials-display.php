<?php declare(strict_types=1);
/**
 * Author Credentials Display Diagnostic
 *
 * Philosophy: Visible credentials build trust
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Author_Credentials_Display {
    public static function check() {
        return [
            'id' => 'seo-author-credentials-display',
            'title' => 'Author Credentials Visibility',
            'description' => 'Display author credentials, certifications, and professional background prominently on content.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/author-credentials/',
            'training_link' => 'https://wpshadow.com/training/expertise-signals/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
