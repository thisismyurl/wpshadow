<?php declare(strict_types=1);
/**
 * Action Schema Diagnostic
 *
 * Philosophy: Action schema enables rich interactions
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Action_Schema {
    public static function check() {
        return [
            'id' => 'seo-action-schema',
            'title' => 'Action Schema Markup',
            'description' => 'Add Action schema (SearchAction, ViewAction) for enhanced search features.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/action-schema/',
            'training_link' => 'https://wpshadow.com/training/interactive-schema/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
