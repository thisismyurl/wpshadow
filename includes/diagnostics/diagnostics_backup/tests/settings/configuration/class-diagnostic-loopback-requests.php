<?php
/**
 * Diagnostic: Loopback Requests (Internal Site Access)
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
 * Diagnostic_LoopbackRequests Class
 */
class Diagnostic_LoopbackRequests extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'loopback-requests';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Loopback Requests (Internal Site Access)';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Detect if WordPress can reach its own loopback URL for cron, REST API, and background tasks';

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
        // Test loopback request to REST API
        $test_url = rest_url( 'wp/v2/types' );
        
        $response = wp_remote_get( $test_url, array(
            'timeout'     => 10,
            'redirection' => 5,
            'sslverify'   => false, // Don't fail on self-signed certs in testing
            'headers'     => array(
                'Cache-Control' => 'no-cache',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %s: error message */
                    __( 'WordPress cannot reach its own URL for internal requests. Error: %s. This breaks scheduled tasks, background updates, and plugin functionality.', 'wpshadow' ),
                    $error_message
                ),
                'severity'      => 'high',
                'threat_level'  => 60,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/configuration-loopback-requests',
                'manual_steps'  => array(
                    __( 'Check if localhost/127.0.0.1 is blocked by firewall', 'wpshadow' ),
                    __( 'Verify server can resolve its own domain name', 'wpshadow' ),
                    __( 'Add domain to /etc/hosts if using local DNS', 'wpshadow' ),
                    __( 'Contact hosting provider if issue persists', 'wpshadow' ),
                ),
                'impact'        => array(
                    'cron'          => __( 'WordPress cron will not run scheduled tasks', 'wpshadow' ),
                    'updates'       => __( 'Background updates may fail', 'wpshadow' ),
                    'plugins'       => __( 'Plugin background processes will not work', 'wpshadow' ),
                    'rest_api'      => __( 'REST API internal requests fail', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'test_url'      => $test_url,
                    'error_message' => $error_message,
                    'error_code'    => $response->get_error_code(),
                    'site_url'      => site_url(),
                    'home_url'      => home_url(),
                ),
            );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        
        // Accept 200 or 401 (REST API might require auth, but connection works)
        if ( ! in_array( $response_code, array( 200, 401 ), true ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: HTTP response code */
                    __( 'WordPress loopback requests return HTTP %d instead of 200. This may indicate server configuration issues affecting background tasks.', 'wpshadow' ),
                    $response_code
                ),
                'severity'      => 'medium',
                'threat_level'  => 50,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/configuration-loopback-requests',
                'manual_steps'  => array(
                    __( 'Check for redirect loops in server configuration', 'wpshadow' ),
                    __( 'Verify SSL certificates are valid', 'wpshadow' ),
                    __( 'Check .htaccess rules for blocking patterns', 'wpshadow' ),
                ),
                'impact'        => array(
                    'functionality' => __( 'Some background processes may not work correctly', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'test_url'      => $test_url,
                    'response_code' => $response_code,
                    'response_body' => wp_remote_retrieve_body( $response ),
                ),
            );
        }

        // Loopback requests are working
        return null;
    }
}
