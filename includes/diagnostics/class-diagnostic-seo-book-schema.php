<?php declare(strict_types=1);
/**
 * Book Schema Diagnostic
 *
 * Philosophy: Book schema for book reviews/sales
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Book_Schema {
    public static function check() {
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
