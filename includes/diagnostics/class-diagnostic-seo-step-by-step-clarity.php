<?php declare(strict_types=1);
/**
 * Step-by-Step Clarity Diagnostic
 *
 * Philosophy: Clear steps win HowTo snippets
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Step_by_Step_Clarity {
    public static function check() {
        return [
            'id' => 'seo-step-by-step-clarity',
            'title' => 'Step-by-Step Content Clarity',
            'description' => 'Format how-to content with numbered steps and HowTo schema for rich results.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/howto-content/',
            'training_link' => 'https://wpshadow.com/training/instructional-content/',
            'auto_fixable' => false,
            'threat_level' => 30,
        ];
    }
}
