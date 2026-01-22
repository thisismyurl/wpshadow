<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Combobox/Autocomplete UX
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-combobox-autocomplete-ux
 * Training: https://wpshadow.com/training/design-combobox-autocomplete-ux
 */
class Diagnostic_Design_COMBOBOX_AUTOCOMPLETE_UX extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-combobox-autocomplete-ux',
            'title' => __('Combobox/Autocomplete UX', 'wpshadow'),
            'description' => __('Validates autocomplete shows suggestions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-combobox-autocomplete-ux',
            'training_link' => 'https://wpshadow.com/training/design-combobox-autocomplete-ux',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
