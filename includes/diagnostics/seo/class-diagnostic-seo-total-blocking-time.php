<?php
declare(strict_types=1);
/**
 * Total Blocking Time Diagnostic
 *
 * Philosophy: Long tasks block user interaction
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Total_Blocking_Time extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-total-blocking-time',
            'title' => 'Total Blocking Time (TBT)',
            'description' => 'TBT should be under 300ms. Break up long JavaScript tasks and defer non-critical code.',
            'severity' => 'high',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/total-blocking-time/',
            'training_link' => 'https://wpshadow.com/training/main-thread-optimization/',
            'auto_fixable' => false,
            'threat_level' => 65,
        ];
    }

}