<?php
declare(strict_types=1);
/**
 * Download Prompts Mobile Diagnostic
 *
 * Philosophy: Aggressive app prompts hurt UX
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Download_Prompts_Mobile extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-download-prompts-mobile',
            'title' => 'App Download Prompt Intrusiveness',
            'description' => 'Avoid aggressive app download prompts that obstruct content. Use subtle banners instead.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/app-install-banners/',
            'training_link' => 'https://wpshadow.com/training/mobile-interstitials/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }

}