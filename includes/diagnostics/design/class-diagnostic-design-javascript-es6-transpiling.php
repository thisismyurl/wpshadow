<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: JavaScript ES6 Transpiling
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-javascript-es6-transpiling
 * Training: https://wpshadow.com/training/design-javascript-es6-transpiling
 */
class Diagnostic_Design_JAVASCRIPT_ES6_TRANSPILING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-javascript-es6-transpiling',
            'title' => __('JavaScript ES6 Transpiling', 'wpshadow'),
            'description' => __('Checks ES6+ code transpiled for older browsers.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-javascript-es6-transpiling',
            'training_link' => 'https://wpshadow.com/training/design-javascript-es6-transpiling',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
