<?php
declare(strict_types=1);
/**
 * IndexNow Readiness Diagnostic
 *
 * Philosophy: Opt-in instant indexing ping capability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_IndexNow_Readiness extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-indexnow-readiness',
            'title' => 'IndexNow Readiness',
            'description' => 'Consider implementing IndexNow protocol for instant indexing notifications to supported search engines.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/indexnow/',
            'training_link' => 'https://wpshadow.com/training/indexation-optimization/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }

}