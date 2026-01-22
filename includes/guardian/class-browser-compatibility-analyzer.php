<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Browser Compatibility Analyzer
 * 
 * Monitors browser usage patterns and detects JavaScript errors to identify
 * compatibility issues affecting users.
 * 
 * Philosophy: Show value (#9) - Ensure site works for all visitors.
 * 
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class Browser_Compatibility_Analyzer {
    
    /**
     * Initialize browser monitoring
     * 
     * @return void
     */
    public static function init(): void {
        // Track browser usage
        add_action('init', [__CLASS__, 'track_browser'], 1);
        
        // Add JavaScript error tracking endpoint
        add_action('rest_api_init', [__CLASS__, 'register_error_endpoint']);
        
        // Run hourly analysis
        if (!wp_next_scheduled('wpshadow_analyze_browser_compat')) {
            wp_schedule_event(time(), 'hourly', 'wpshadow_analyze_browser_compat');
        }
        add_action('wpshadow_analyze_browser_compat', [__CLASS__, 'analyze']);
    }
    
    /**
     * Track browser usage
     * 
     * @return void
     */
    public static function track_browser(): void {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (empty($user_agent)) {
            return;
        }
        
        // Skip bots
        if (preg_match('/(bot|crawler|spider)/i', $user_agent)) {
            return;
        }
        
        $browser_info = self::parse_user_agent($user_agent);
        
        $browsers = get_transient('wpshadow_browser_usage_data');
        if (!is_array($browsers)) {
            $browsers = array();
        }
        
        $browsers[] = array(
            'name' => $browser_info['name'],
            'version' => $browser_info['version'],
            'platform' => $browser_info['platform'],
            'timestamp' => time(),
        );
        
        // Keep only last 24 hours
        $one_day_ago = time() - DAY_IN_SECONDS;
        $browsers = array_filter($browsers, function($item) use ($one_day_ago) {
            return $item['timestamp'] > $one_day_ago;
        });
        
        // Limit to 1000 entries
        if (count($browsers) > 1000) {
            $browsers = array_slice($browsers, -1000);
        }
        
        set_transient('wpshadow_browser_usage_data', $browsers, DAY_IN_SECONDS);
    }
    
    /**
     * Parse user agent string
     * 
     * @param string $user_agent User agent
     * @return array Browser info
     */
    private static function parse_user_agent(string $user_agent): array {
        $info = array(
            'name' => 'Unknown',
            'version' => '0',
            'platform' => 'Unknown',
        );
        
        // Detect browser
        if (preg_match('/Edge\/(\d+)/i', $user_agent, $m)) {
            $info['name'] = 'Edge';
            $info['version'] = $m[1];
        } elseif (preg_match('/Edg\/(\d+)/i', $user_agent, $m)) {
            $info['name'] = 'Edge Chromium';
            $info['version'] = $m[1];
        } elseif (preg_match('/Chrome\/(\d+)/i', $user_agent, $m)) {
            $info['name'] = 'Chrome';
            $info['version'] = $m[1];
        } elseif (preg_match('/Safari\/(\d+)/i', $user_agent, $m) && !preg_match('/Chrome/i', $user_agent)) {
            $info['name'] = 'Safari';
            $info['version'] = $m[1];
        } elseif (preg_match('/Firefox\/(\d+)/i', $user_agent, $m)) {
            $info['name'] = 'Firefox';
            $info['version'] = $m[1];
        } elseif (preg_match('/MSIE (\d+)|Trident.*rv:(\d+)/i', $user_agent, $m)) {
            $info['name'] = 'Internet Explorer';
            $info['version'] = $m[1] ?? $m[2] ?? '0';
        }
        
        // Detect platform
        if (preg_match('/Windows/i', $user_agent)) {
            $info['platform'] = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $user_agent)) {
            $info['platform'] = 'macOS';
        } elseif (preg_match('/Linux/i', $user_agent)) {
            $info['platform'] = 'Linux';
        } elseif (preg_match('/Android/i', $user_agent)) {
            $info['platform'] = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $user_agent)) {
            $info['platform'] = 'iOS';
        }
        
        return $info;
    }
    
    /**
     * Register REST API endpoint for JavaScript errors
     * 
     * @return void
     */
    public static function register_error_endpoint(): void {
        register_rest_route('wpshadow/v1', '/js-error', array(
            'methods' => 'POST',
            'callback' => [__CLASS__, 'handle_js_error'],
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * Handle JavaScript error report
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public static function handle_js_error($request): \WP_REST_Response {
        $body = $request->get_json_params();
        
        if (empty($body['message'])) {
            return new \WP_REST_Response(array('status' => 'invalid'), 400);
        }
        
        $errors = get_transient('wpshadow_js_errors');
        if (!is_array($errors)) {
            $errors = array();
        }
        
        $errors[] = array(
            'message' => substr($body['message'], 0, 500),
            'file' => substr($body['file'] ?? '', 0, 200),
            'line' => (int) ($body['line'] ?? 0),
            'browser' => substr($body['browser'] ?? '', 0, 100),
            'timestamp' => time(),
        );
        
        // Keep only last 100 errors
        if (count($errors) > 100) {
            $errors = array_slice($errors, -100);
        }
        
        set_transient('wpshadow_js_errors', $errors, DAY_IN_SECONDS);
        
        return new \WP_REST_Response(array('status' => 'recorded'), 204);
    }
    
    /**
     * Analyze browser compatibility
     * 
     * @return array Analysis results
     */
    public static function analyze(): array {
        $browser_data = get_transient('wpshadow_browser_usage_data');
        $js_errors = get_transient('wpshadow_js_errors');
        
        $results = array(
            'total_visits' => 0,
            'unique_browsers' => 0,
            'browser_breakdown' => array(),
            'old_browsers' => array(),
            'js_error_count' => 0,
            'has_compatibility_issues' => false,
        );
        
        // Analyze browser usage
        if (is_array($browser_data) && !empty($browser_data)) {
            $results['total_visits'] = count($browser_data);
            
            // Count by browser
            $by_browser = array();
            foreach ($browser_data as $visit) {
                $key = $visit['name'] . ' ' . $visit['version'];
                if (!isset($by_browser[$key])) {
                    $by_browser[$key] = array(
                        'name' => $visit['name'],
                        'version' => $visit['version'],
                        'count' => 0,
                    );
                }
                $by_browser[$key]['count']++;
                
                // Check for old browsers
                if (self::is_old_browser($visit['name'], (int) $visit['version'])) {
                    $results['old_browsers'][] = $key;
                }
            }
            
            $results['unique_browsers'] = count($by_browser);
            $results['browser_breakdown'] = $by_browser;
        }
        
        // Analyze JS errors
        if (is_array($js_errors)) {
            $results['js_error_count'] = count($js_errors);
            $results['has_compatibility_issues'] = count($js_errors) > 10 || !empty($results['old_browsers']);
        }
        
        // Set transient for diagnostic
        set_transient('wpshadow_browser_issues', $results, HOUR_IN_SECONDS);
        
        return $results;
    }
    
    /**
     * Check if browser version is old/unsupported
     * 
     * @param string $name Browser name
     * @param int $version Browser version
     * @return bool True if old
     */
    private static function is_old_browser(string $name, int $version): bool {
        $min_versions = array(
            'Chrome' => 90,
            'Firefox' => 88,
            'Safari' => 13,
            'Edge' => 90,
            'Edge Chromium' => 90,
            'Internet Explorer' => 999, // All IE is old
        );
        
        if (!isset($min_versions[$name])) {
            return false;
        }
        
        return $version < $min_versions[$name];
    }
    
    /**
     * Get summary
     * 
     * @return array Summary data
     */
    public static function get_summary(): array {
        $results = get_transient('wpshadow_browser_issues');
        return is_array($results) ? $results : array(
            'total_visits' => 0,
            'js_error_count' => 0,
            'has_compatibility_issues' => false,
        );
    }
    
    /**
     * Clear cached data
     * 
     * @return void
     */
    public static function clear_cache(): void {
        delete_transient('wpshadow_browser_usage_data');
        delete_transient('wpshadow_js_errors');
        delete_transient('wpshadow_browser_issues');
    }
}
