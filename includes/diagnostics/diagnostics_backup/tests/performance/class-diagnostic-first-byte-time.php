<?php
/**
 * Diagnostic: First Byte Time (TTFB) Measurement
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
 * Diagnostic_FirstByteTime Class
 */
class Diagnostic_FirstByteTime extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'first-byte-time';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'First Byte Time (TTFB) Measurement';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Measure Time To First Byte (TTFB) to identify server/PHP performance issues';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'performance';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Measure TTFB by making a request to the homepage
        $start_time = microtime( true );
        
        $response = wp_remote_get( home_url( '/' ), array(
            'timeout'     => 10,
            'redirection' => 5,
            'sslverify'   => false,
            'headers'     => array(
                'Cache-Control' => 'no-cache',
            ),
        ) );
        
        $end_time = microtime( true );
        
        if ( is_wp_error( $response ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %s: error message */
                    __( 'Could not measure TTFB: %s', 'wpshadow' ),
                    $response->get_error_message()
                ),
                'severity'      => 'medium',
                'threat_level'  => 50,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/performance-first-byte-time',
                'manual_steps'  => array(
                    __( 'Check if site is accessible', 'wpshadow' ),
                    __( 'Verify loopback requests are working', 'wpshadow' ),
                ),
                'impact'        => array(
                    'measurement' => __( 'Cannot measure server response time', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'error' => $response->get_error_message(),
                ),
            );
        }
        
        // Calculate TTFB in milliseconds
        $ttfb_seconds = $end_time - $start_time;
        $ttfb_ms = round( $ttfb_seconds * 1000, 2 );
        
        // Determine threat level based on TTFB
        $threat_level = 0;
        $severity = 'good';
        
        if ( $ttfb_ms > 1000 ) {
            $threat_level = 50; // Slow server
            $severity = 'medium';
        } elseif ( $ttfb_ms > 500 ) {
            $threat_level = 30; // Acceptable but room for improvement
            $severity = 'low';
        }
        
        // If TTFB is good (< 500ms), no issue
        if ( $threat_level === 0 ) {
            return null;
        }
        
        $suggestions = array();
        
        if ( $ttfb_ms > 1000 ) {
            $suggestions[] = __( 'Enable caching (object cache, page cache)', 'wpshadow' );
            $suggestions[] = __( 'Review slow database queries', 'wpshadow' );
            $suggestions[] = __( 'Consider upgrading hosting plan', 'wpshadow' );
        } else {
            $suggestions[] = __( 'Consider enabling object caching', 'wpshadow' );
            $suggestions[] = __( 'Review plugin performance', 'wpshadow' );
        }
        
        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => sprintf(
                /* translators: %s: TTFB in milliseconds */
                __( 'Time To First Byte is %sms. This indicates server response time before any content is sent. Faster is better.', 'wpshadow' ),
                number_format_i18n( $ttfb_ms, 2 )
            ),
            'severity'      => $severity,
            'threat_level'  => $threat_level,
            'auto_fixable'  => false,
            'kb_link'       => 'https://wpshadow.com/kb/performance-first-byte-time',
            'manual_steps'  => $suggestions,
            'impact'        => array(
                'seo'         => __( 'Page speed affects search rankings', 'wpshadow' ),
                'users'       => __( 'Slow TTFB creates poor user experience', 'wpshadow' ),
                'performance' => __( 'High TTFB indicates server/PHP bottleneck', 'wpshadow' ),
            ),
            'evidence'      => array(
                'ttfb_ms'       => $ttfb_ms,
                'ttfb_seconds'  => $ttfb_seconds,
                'response_code' => wp_remote_retrieve_response_code( $response ),
            ),
        );
    }
}
