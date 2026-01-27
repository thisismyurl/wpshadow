<?php
/**
 * Diagnostic: Feed availability
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
 * Diagnostic_FeedAvailability Class
 */
class Diagnostic_FeedAvailability extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'feed-availability';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Feed availability';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Feed availability';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'monitoring';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if feeds are disabled in WordPress settings
        $rss_use_excerpt = get_option( 'rss_use_excerpt' );
        
        // Get primary feed URLs
        $rss_url = get_feed_link( 'rss2' );
        $atom_url = get_feed_link( 'atom' );
        
        // Test RSS2 feed availability
        $rss_response = wp_remote_head( $rss_url, array(
            'timeout' => 10,
            'redirection' => 5,
        ) );

        $rss_available = ! is_wp_error( $rss_response ) && 200 === wp_remote_retrieve_response_code( $rss_response );

        // Test Atom feed availability
        $atom_response = wp_remote_head( $atom_url, array(
            'timeout' => 10,
            'redirection' => 5,
        ) );

        $atom_available = ! is_wp_error( $atom_response ) && 200 === wp_remote_retrieve_response_code( $atom_response );

        // If both feeds are working, no issue
        if ( $rss_available && $atom_available ) {
            return null;
        }

        $issues = array();
        
        if ( ! $rss_available ) {
            $error_msg = is_wp_error( $rss_response ) ? $rss_response->get_error_message() : __( 'HTTP error', 'wpshadow' );
            $issues[] = sprintf(
                /* translators: %s: error message */
                __( 'RSS feed not accessible: %s', 'wpshadow' ),
                $error_msg
            );
        }

        if ( ! $atom_available ) {
            $error_msg = is_wp_error( $atom_response ) ? $atom_response->get_error_message() : __( 'HTTP error', 'wpshadow' );
            $issues[] = sprintf(
                /* translators: %s: error message */
                __( 'Atom feed not accessible: %s', 'wpshadow' ),
                $error_msg
            );
        }

        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => sprintf(
                /* translators: %s: list of issues */
                __( 'Your site feeds are not accessible: %s. RSS feeds allow readers and services to subscribe to your content updates.', 'wpshadow' ),
                implode( '; ', $issues )
            ),
            'severity'      => 'medium',
            'threat_level'  => 30,
            'auto_fixable'  => false,
            'kb_link'       => 'https://wpshadow.com/kb/feed-availability',
            'manual_steps'  => array(
                __( 'Check if feeds are blocked in your .htaccess or server configuration', 'wpshadow' ),
                __( 'Verify no security plugin is blocking feed access', 'wpshadow' ),
                __( 'Test feed URLs directly in your browser', 'wpshadow' ),
                __( 'Check for redirect loops or SSL certificate issues', 'wpshadow' ),
            ),
            'impact'        => array(
                'seo'     => __( 'RSS readers and content aggregators cannot subscribe to your site', 'wpshadow' ),
                'traffic' => __( 'Lost opportunity for syndicated traffic', 'wpshadow' ),
            ),
            'evidence'      => array(
                'rss_url'        => $rss_url,
                'atom_url'       => $atom_url,
                'rss_available'  => $rss_available,
                'atom_available' => $atom_available,
                'rss_error'      => is_wp_error( $rss_response ) ? $rss_response->get_error_message() : null,
                'atom_error'     => is_wp_error( $atom_response ) ? $atom_response->get_error_message() : null,
            ),
        );
    }
}
