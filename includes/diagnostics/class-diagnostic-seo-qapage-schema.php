<?php declare(strict_types=1);
/**
 * QAPage Schema Diagnostic
 *
 * Philosophy: QA schema for forum/FAQ content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_QAPage_Schema {
    public static function check() {
        return [
            'id' => 'seo-qapage-schema',
            'title' => 'QAPage Schema Markup',
            'description' => 'Add QAPage schema for Q&A content: question, answers, upvotes, accepted answer.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/qapage-schema/',
            'training_link' => 'https://wpshadow.com/training/forum-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
