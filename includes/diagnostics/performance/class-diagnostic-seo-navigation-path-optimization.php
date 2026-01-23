<?php
declare(strict_types=1);
/**
 * Navigation Path Optimization Diagnostic
 *
 * Philosophy: Clear paths reduce confusion
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Navigation_Path_Optimization extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-navigation-path-optimization',
            'title' => 'Navigation Path Clarity',
            'description' => 'Ensure clear navigation paths. Use analytics to identify drop-off points.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/navigation-paths/',
            'training_link' => 'https://wpshadow.com/training/user-flow-optimization/',
            'auto_fixable' => false,
            'threat_level' => 30,
        ];
    }

}