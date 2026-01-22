<?php
declare(strict_types=1);
/**
 * Interactive Elements Engagement Diagnostic
 *
 * Philosophy: Interactive elements increase engagement
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Interactive_Elements_Engagement extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-interactive-elements-engagement',
            'title' => 'Interactive Content Elements',
            'description' => 'Add calculators, quizzes, polls, or interactive tools to increase engagement.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/interactive-content/',
            'training_link' => 'https://wpshadow.com/training/engagement-strategies/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
