<?php declare(strict_types=1);
/**
 * FAQ Schema Validity Diagnostic
 *
 * Philosophy: Use FAQPage markup only for real FAQs
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_FAQ_Schema_Validity {
    public static function check() {
        return [
            'id' => 'seo-faq-schema-validity',
            'title' => 'FAQ Schema Validity',
            'description' => 'Ensure FAQPage schema is used appropriately and limited to pages that genuinely contain FAQs.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/faq-schema-guidelines/',
            'training_link' => 'https://wpshadow.com/training/schema-serp-features/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
