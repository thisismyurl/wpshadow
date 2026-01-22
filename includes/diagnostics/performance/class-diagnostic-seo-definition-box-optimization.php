<?php
declare(strict_types=1);
/**
 * Definition Box Optimization Diagnostic
 *
 * Philosophy: Concise definitions win featured snippets
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Definition_Box_Optimization extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-definition-box-optimization',
            'title' => 'Definition Featured Snippet Optimization',
            'description' => 'Provide concise 40-60 word definitions in first paragraph for featured snippets.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/featured-snippets/',
            'training_link' => 'https://wpshadow.com/training/snippet-optimization/',
            'auto_fixable' => false,
            'threat_level' => 30,
        ];
    }
}
