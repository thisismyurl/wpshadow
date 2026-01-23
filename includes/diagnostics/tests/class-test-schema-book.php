<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Book extends Diagnostic_Base {
    
    protected static $slug = 'test-schema-book';
    protected static $title = 'Book Schema Test';
    protected static $description = 'Tests for Book structured data';
    
    public static function check(?string $url = null, ?string $html = null): ?array {
        if ($html !== null) {
            return self::analyze_html($html, $url ?? 'provided-html');
        }
        
        $html = self::fetch_html($url ?? home_url('/'));
        if ($html === false) {
            return null;
        }
        
        return self::analyze_html($html, $url ?? home_url('/'));
    }
    
    protected static function analyze_html(string $html, string $checked_url): ?array {
        // Check for book indicators
        $has_book_keywords = preg_match('/\b(book|novel|author|ISBN|published|publisher|pages?|edition|hardcover|paperback|kindle)\b/i', $html);
        $has_isbn = preg_match('/ISBN[\s:-]*[0-9-]{10,17}/i', $html);
        $has_author_mention = preg_match('/\b(by|author:|written by)\b/i', $html);
        
        // Check for Book schema
        $has_book_schema = preg_match('/"@type"\s*:\s*"Book"/i', $html);
        
        // If looks like book page but no schema
        if ($has_book_keywords && ($has_isbn || $has_author_mention) && !$has_book_schema) {
            return [
                'id' => 'schema-book-missing',
                'title' => 'Book Schema Missing',
                'description' => 'Book content detected but no Book structured data found. Book schema enables rich results with author, ISBN, and review information.',
                'color' => '#2196f3',
                'bg_color' => '#e3f2fd',
                'kb_link' => 'https://wpshadow.com/kb/book-schema/',
                'training_link' => 'https://wpshadow.com/training/structured-data/',
                'auto_fixable' => false,
                'threat_level' => 30,
                'module' => 'SEO',
                'priority' => 3,
                'meta' => [
                    'has_book_keywords' => $has_book_keywords,
                    'has_isbn' => $has_isbn,
                    'has_author' => $has_author_mention,
                    'has_schema' => $has_book_schema,
                    'checked_url' => $checked_url,
                ],
            ];
        }
        
        return null;
    }
    
    protected static function fetch_html(string $url) {
        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
        return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
    }
    
    public static function get_name(): string {
        return __('Book Schema', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks for Book structured data (publishers, authors).', 'wpshadow');
    }
}
