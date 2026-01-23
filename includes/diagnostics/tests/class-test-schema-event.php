<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Event extends Diagnostic_Base {
    
    protected static $slug = 'test-schema-event';
    protected static $title = 'Event Schema Test';
    protected static $description = 'Tests for Event structured data';
    
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
        // Check for event indicators
        $has_event_keywords = preg_match('/\b(event|conference|webinar|workshop|seminar|meetup)\b/i', $html);
        $has_date_time = preg_match('/<time[^>]*datetime|class=["\'][^"\']*date/i', $html);
        $has_location = preg_match('/\b(venue|location|address)\b/i', $html);
        
        // Check for Event schema
        $has_event_schema = preg_match('/"@type"\s*:\s*"Event"/i', $html);
        
        // If looks like event but no schema
        if ($has_event_keywords && $has_date_time && $has_location && !$has_event_schema) {
            return [
                'id' => 'schema-event-missing',
                'title' => 'Event Schema Missing',
                'description' => 'Event content detected (date, location, event keywords) but no Event structured data found. Event schema enables rich results with date/location in search.',
                'color' => '#2196f3',
                'bg_color' => '#e3f2fd',
                'kb_link' => 'https://wpshadow.com/kb/event-schema/',
                'training_link' => 'https://wpshadow.com/training/structured-data/',
                'auto_fixable' => false,
                'threat_level' => 35,
                'module' => 'SEO',
                'priority' => 3,
                'meta' => [
                    'has_event_keywords' => $has_event_keywords,
                    'has_date_time' => $has_date_time,
                    'has_location' => $has_location,
                    'has_schema' => $has_event_schema,
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
        return __('Event Schema', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks for Event structured data.', 'wpshadow');
    }
}
