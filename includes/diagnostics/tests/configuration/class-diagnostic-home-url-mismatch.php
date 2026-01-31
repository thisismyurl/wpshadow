<?php
/**
 * Diagnostic: Home URL Mismatch
 *
 * @since 1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Diagnostic_HomeUrlMismatch Class
 */
class Diagnostic_HomeUrlMismatch extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'home-url-mismatch';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Home URL Mismatch';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Detect if WordPress home_url() matches the actual site URL';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'configuration';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        $home_url = home_url();
        $site_url = site_url();
        
        // Get actual request URL
        $is_https = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'];
        $protocol = $is_https ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $actual_url = $protocol . $host;
        
        // Parse URLs for comparison
        $home_parsed = wp_parse_url( $home_url );
        $site_parsed = wp_parse_url( $site_url );
        $actual_parsed = wp_parse_url( $actual_url );
        
        $home_scheme = $home_parsed['scheme'] ?? 'http';
        $home_host = $home_parsed['host'] ?? '';
        $actual_scheme = $actual_parsed['scheme'] ?? 'http';
        $actual_host = $actual_parsed['host'] ?? '';
        
        $issues = array();
        
        // Check for scheme mismatch (HTTP vs HTTPS)
        if ( $home_scheme !== $actual_scheme ) {
            $issues[] = sprintf(
                /* translators: 1: configured scheme, 2: actual scheme */
                __( 'Protocol mismatch: configured as %1$s but accessed via %2$s', 'wpshadow' ),
                $home_scheme,
                $actual_scheme
            );
        }
        
        // Check for host mismatch
        if ( strtolower( $home_host ) !== strtolower( $actual_host ) ) {
            $issues[] = sprintf(
                /* translators: 1: configured host, 2: actual host */
                __( 'Domain mismatch: configured as %1$s but accessed via %2$s', 'wpshadow' ),
                $home_host,
                $actual_host
            );
        }
        
        // Check if home_url and site_url differ (also problematic)
        if ( $home_url !== $site_url ) {
            $issues[] = sprintf(
                /* translators: 1: home URL, 2: site URL */
                __( 'Home URL and Site URL differ: %1$s vs %2$s', 'wpshadow' ),
                $home_url,
                $site_url
            );
        }
        
        
		// Check 4: Feature enabled
		if ( ! (get_option( "home-url-mismatch_enabled" ) === "1") ) {
			$issues[] = __( 'Feature enabled', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
            return null; // No mismatch
        }
        
        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => sprintf(
                /* translators: %s: list of issues */
                __( 'WordPress URL configuration issues detected: %s. This can cause 404 errors, broken links, and admin access problems.', 'wpshadow' ),
                implode( '; ', $issues )
            ),
            'severity'      => 'high',
            'threat_level'  => 70,
            'auto_fixable'  => true,
            'kb_link'       => 'https://wpshadow.com/kb/configuration-home-url-mismatch',
            'manual_steps'  => array(
                __( 'Update WordPress Address and Site Address in Settings > General', 'wpshadow' ),
                __( 'Or define WP_HOME and WP_SITEURL in wp-config.php', 'wpshadow' ),
                __( 'Clear browser cache and try again', 'wpshadow' ),
            ),
            'impact'        => array(
                'accessibility' => __( 'Site may be inaccessible with 404 errors', 'wpshadow' ),
                'admin'         => __( 'Admin area may not load correctly', 'wpshadow' ),
                'links'         => __( 'Internal links will be broken', 'wpshadow' ),
            ),
            'evidence'      => array(
                'home_url'    => $home_url,
                'site_url'    => $site_url,
                'actual_url'  => $actual_url,
                'issues'      => $issues,
            ),
        );
    }
}
