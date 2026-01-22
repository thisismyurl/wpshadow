<?php
declare(strict_types=1);
/**
 * List Format Optimization Diagnostic
 *
 * Philosophy: Lists win snippet positions
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_List_Format_Optimization extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-list-format-optimization',
            'title' => 'List Featured Snippet Optimization',
            'description' => 'Use ordered/unordered lists with clear items for list-based featured snippets.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/list-snippets/',
            'training_link' => 'https://wpshadow.com/training/list-content/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
