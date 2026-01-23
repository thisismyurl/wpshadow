<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Third Party Script Analyzer
 * 
 * Analyzes third-party scripts loaded on the site to detect performance impact.
 * Identifies external scripts from analytics, ads, tracking, and other services.
 * 
 * Philosophy: Show value (#9) - Identify performance bottlenecks from external scripts.
 * 
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class Third_Party_Script_Analyzer {
    
    /**
     * Known third-party script domains
     * 
     * @var array
     */
    private static $third_party_domains = array(
        'google-analytics.com',
        'googletagmanager.com',
        'facebook.net',
        'connect.facebook.net',
        'doubleclick.net',
        'googlesyndication.com',
        'analytics.tiktok.com',
        'cdn.jsdelivr.net',
        'unpkg.com',
        'cdnjs.cloudflare.com',
        'ajax.googleapis.com',
        'code.jquery.com',
        'maxcdn.bootstrapcdn.com',
        'stackpath.bootstrapcdn.com',
        'twitter.com',
        'linkedin.com',
        'pinterest.com',
        'hotjar.com',
        'crazyegg.com',
        'optimizely.com',
        'clarity.ms',
    );
    
    /**
     * Analyze third-party scripts
     * 
     * @return array Analysis results
     */
    public static function analyze(): array {
        // Check cache first (24 hours)
        $cached = get_transient('wpshadow_third_party_script_analysis');
        if ($cached && is_array($cached)) {
            return $cached;
        }
        
        $results = array(
            'third_party_count' => 0,
            'total_scripts' => 0,
            'domains' => array(),
            'scripts' => array(),
        );
        
        // Get enqueued scripts
        global $wp_scripts;
        
        if (!isset($wp_scripts) || !($wp_scripts instanceof \WP_Scripts)) {
            set_transient('wpshadow_third_party_script_analysis', $results, DAY_IN_SECONDS);
            return $results;
        }
        
        $site_domain = parse_url(get_site_url(), PHP_URL_HOST);
        
        foreach ($wp_scripts->registered as $handle => $script) {
            if (!is_string($script->src) || empty($script->src)) {
                continue;
            }
            
            $results['total_scripts']++;
            
            // Check if external
            if (self::is_third_party_script($script->src, $site_domain)) {
                $results['third_party_count']++;
                
                // Extract domain
                $parsed = parse_url($script->src);
                if (isset($parsed['host'])) {
                    $domain = $parsed['host'];
                    if (!isset($results['domains'][$domain])) {
                        $results['domains'][$domain] = 0;
                    }
                    $results['domains'][$domain]++;
                    
                    $results['scripts'][] = array(
                        'handle' => $handle,
                        'domain' => $domain,
                        'src' => $script->src,
                    );
                }
            }
        }
        
        // Set transients
        set_transient('wpshadow_third_party_script_count', $results['third_party_count'], DAY_IN_SECONDS);
        set_transient('wpshadow_third_party_script_analysis', $results, DAY_IN_SECONDS);
        
        return $results;
    }
    
    /**
     * Check if script is from third party
     * 
     * @param string $src Script source URL
     * @param string $site_domain Site domain
     * @return bool True if third party
     */
    private static function is_third_party_script(string $src, string $site_domain): bool {
        // Absolute URL check
        if (strpos($src, 'http://') === 0 || strpos($src, 'https://') === 0) {
            $parsed = parse_url($src);
            if (isset($parsed['host'])) {
                $script_domain = $parsed['host'];
                
                // Not third party if same domain
                if ($script_domain === $site_domain) {
                    return false;
                }
                
                // Check against known third-party domains
                foreach (self::$third_party_domains as $tp_domain) {
                    if (strpos($script_domain, $tp_domain) !== false) {
                        return true;
                    }
                }
                
                // Any external domain is third party
                return true;
            }
        }
        
        // Relative URLs are not third party
        return false;
    }
    
    /**
     * Clear cached data
     * 
     * @return void
     */
    public static function clear_cache(): void {
        delete_transient('wpshadow_third_party_script_count');
        delete_transient('wpshadow_third_party_script_analysis');
    }
}
