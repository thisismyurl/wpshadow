<?php
declare(strict_types=1);
/**
 * Book Schema Diagnostic
 *
 * Philosophy: Book schema for book reviews/sales
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Book_Schema extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-book-schema',
            'title' => 'Book Schema Markup',
            'description' => 'Add Book schema for book content: author, ISBN, reviews, offers.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/book-schema/',
            'training_link' => 'https://wpshadow.com/training/book-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
