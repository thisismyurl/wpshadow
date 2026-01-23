<?php
declare(strict_types=1);
/**
 * QAPage Schema Diagnostic
 *
 * Philosophy: QA schema for forum/FAQ content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_QAPage_Schema extends Diagnostic_Base {
    public static function check(): ?array {
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