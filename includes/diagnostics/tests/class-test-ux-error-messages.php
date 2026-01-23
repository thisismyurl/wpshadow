<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Error_Messages extends Diagnostic_Base {
    
    protected static $slug = 'test-ux-error-messages';
    protected static $title = 'Form Error Message Test';
    protected static $description = 'Tests for helpful form validation messages';
    
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
        // Check for forms
        $has_forms = preg_match('/<form[^>]*>/i', $html);
        if (!$has_forms) {
            return null;
        }
        
        // Check for error message containers
        $has_error_container = preg_match('/class=["\'][^"\']*error|aria-live=["\']polite|role=["\']alert/i', $html);
        
        // Check for required field indicators
        $has_required = preg_match('/required|aria-required=["\']true/i', $html);
        
        // Check for HTML5 validation attributes
        $has_validation = preg_match('/pattern=|minlength=|maxlength=|min=|max=/i', $html);
        
        // If form exists but minimal error handling
        if ($has_required && !$has_error_container) {
            return [
                'id' => 'ux-error-messages-missing',
                'title' => 'Form Error Messages Not Configured',
                'description' => 'Forms with required fields found but no visible error message containers (role="alert" or aria-live). Users need clear feedback when validation fails.',
                'color' => '#ff9800',
                'bg_color' => '#fff3e0',
                'kb_link' => 'https://wpshadow.com/kb/form-validation/',
                'training_link' => 'https://wpshadow.com/training/accessible-forms/',
                'auto_fixable' => false,
                'threat_level' => 45,
                'module' => 'UX',
                'priority' => 2,
                'meta' => [
                    'has_required' => $has_required,
                    'has_error_container' => $has_error_container,
                    'has_validation' => $has_validation,
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
        return __('Form Error Messages', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks for helpful form validation error messages.', 'wpshadow');
    }
}
