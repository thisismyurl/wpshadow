<?php
declare(strict_types=1);
/**
 * Third-Party Script Impact Diagnostic
 *
 * Philosophy: External scripts slow pages
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Third_Party_Script_Impact extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-third-party-script-impact',
            'title' => 'Third-Party Script Performance',
            'description' => 'Audit third-party scripts (ads, analytics, social widgets) for performance impact. Load asynchronously or defer.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/third-party-scripts/',
            'training_link' => 'https://wpshadow.com/training/external-script-management/',
            'auto_fixable' => false,
            'threat_level' => 45,
        ];
    }
}
