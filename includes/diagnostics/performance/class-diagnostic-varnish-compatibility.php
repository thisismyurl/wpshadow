<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Varnish Cache Compatible?
 * 
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Varnish_Compatibility extends Diagnostic_Base {
    protected static $slug = 'varnish-compatibility';
    protected static $title = 'Varnish Cache Compatible?';
    protected static $description = 'Tests compatibility with Varnish caching.';


    public static function check(): ?array {
        return null; // Varnish detection requires server-level access
    }
}
