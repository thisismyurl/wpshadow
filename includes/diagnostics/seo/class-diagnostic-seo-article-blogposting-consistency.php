<?php
declare(strict_types=1);
/**
 * Article BlogPosting Consistency Diagnostic
 *
 * Philosophy: Consistent schema type per template
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Article_BlogPosting_Consistency extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-article-blogposting-consistency',
            'title' => 'Article vs BlogPosting Consistency',
            'description' => 'Use a consistent schema type (Article or BlogPosting) across similar content templates.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/article-schema-consistency/',
            'training_link' => 'https://wpshadow.com/training/structured-data/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }

}