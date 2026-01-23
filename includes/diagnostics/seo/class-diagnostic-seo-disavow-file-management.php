<?php
declare(strict_types=1);
/**
 * Disavow File Management Diagnostic
 *
 * Philosophy: Disavow toxic backlinks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Disavow_File_Management extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-disavow-file-management',
            'title' => 'Disavow File for Toxic Links',
            'description' => 'Maintain disavow file in Search Console for toxic backlinks you cannot remove.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/disavow-file/',
            'training_link' => 'https://wpshadow.com/training/negative-seo/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }

}