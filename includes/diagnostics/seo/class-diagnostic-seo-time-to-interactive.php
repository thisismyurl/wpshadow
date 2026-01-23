<?php
declare(strict_types=1);
/**
 * Time To Interactive Diagnostic
 *
 * Philosophy: Pages must become interactive quickly
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Time_To_Interactive extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-time-to-interactive',
            'title' => 'Time To Interactive (TTI)',
            'description' => 'TTI should be under 3.8s. Reduce JavaScript execution time and defer non-critical scripts.',
            'severity' => 'high',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/time-to-interactive/',
            'training_link' => 'https://wpshadow.com/training/javascript-optimization/',
            'auto_fixable' => false,
            'threat_level' => 70,
        ];
    }

}