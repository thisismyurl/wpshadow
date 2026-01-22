<?php
declare(strict_types=1);
/**
 * Link Context Relevance Diagnostic
 *
 * Philosophy: Links in relevant context are better
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Link_Context_Relevance extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-link-context-relevance',
            'title' => 'Link Contextual Relevance',
            'description' => 'Place links within relevant content context, not footer/sidebar link farms.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/link-context/',
            'training_link' => 'https://wpshadow.com/training/contextual-linking/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
