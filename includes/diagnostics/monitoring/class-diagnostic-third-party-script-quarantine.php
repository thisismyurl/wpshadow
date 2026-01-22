<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Third-Party Script Quarantine Testing (FE-019)
 * 
 * Measures performance impact of each third-party script.
 * Philosophy: Educate (#5) - Know the cost of every tag.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Third_Party_Script_Quarantine extends Diagnostic_Base {
    public static function check(): ?array {
        $quarantined_scripts = get_transient('wpshadow_quarantined_scripts_count');
        
        if ($quarantined_scripts && $quarantined_scripts > 0) {
            return array(
                'id' => 'third-party-script-quarantine',
                'title' => sprintf(__('%d Scripts in Quarantine', 'wpshadow'), $quarantined_scripts),
                'description' => __('Some third-party scripts have been isolated due to performance or security concerns. Review and enable them carefully.', 'wpshadow'),
                'severity' => 'info',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/script-quarantine/',
                'training_link' => 'https://wpshadow.com/training/malicious-script-detection/',
                'auto_fixable' => false,
                'threat_level' => 25,
            );
        }
        return null;
    }
}
