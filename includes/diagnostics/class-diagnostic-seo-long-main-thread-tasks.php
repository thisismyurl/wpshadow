<?php declare(strict_types=1);
/**
 * Long Main Thread Tasks Diagnostic
 *
 * Philosophy: Identify heavy scripts affecting INP
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Long_Main_Thread_Tasks {
    public static function check() {
        return [
            'id' => 'seo-long-main-thread-tasks',
            'title' => 'Long Main Thread Tasks',
            'description' => 'Identify heavy third-party scripts and long main-thread tasks that degrade INP and responsiveness.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/long-tasks-optimization/',
            'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
            'auto_fixable' => false,
            'threat_level' => 45,
        ];
    }
}
