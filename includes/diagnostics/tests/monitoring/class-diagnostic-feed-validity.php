<?php
/**
 * Diagnostic: Feed validity
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
 * Diagnostic_FeedValidity Class
 */
class Diagnostic_FeedValidity extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'feed-validity';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Feed validity';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Feed validity';

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
        $issues = array();

        // Check main RSS feed
        $rss_url = get_feed_link( 'rss2' );
        $rss_response = wp_remote_get( $rss_url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $rss_response ) ) {
            $issues[] = sprintf(
                /* translators: %s: error message */
                __( 'RSS feed unavailable: %s', 'wpshadow' ),
                $rss_response->get_error_message()
            );
        } else {
            $body = wp_remote_retrieve_body( $rss_response );
            
            // Check if it's valid XML
            libxml_use_internal_errors( true );
            $xml = simplexml_load_string( $body );
            
            if ( false === $xml ) {
                $xml_errors = libxml_get_errors();
                $issues[] = sprintf(
                    /* translators: %s: XML error message */
                    __( 'RSS feed contains invalid XML: %s', 'wpshadow' ),
                    ! empty( $xml_errors ) ? $xml_errors[0]->message : __( 'Unknown XML error', 'wpshadow' )
                );
                libxml_clear_errors();
            }
        }

        // Check Atom feed
        $atom_url = get_feed_link( 'atom' );
        $atom_response = wp_remote_get( $atom_url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $atom_response ) ) {
            $issues[] = sprintf(
                /* translators: %s: error message */
                __( 'Atom feed unavailable: %s', 'wpshadow' ),
                $atom_response->get_error_message()
            );
        }

        if ( empty( $issues ) ) {
            return null; // All feeds valid
        }

        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => sprintf(
                /* translators: %s: list of issues */
                __( 'Your site feeds have validation issues: %s. This prevents feed readers and syndication services from accessing your content.', 'wpshadow' ),
                implode( '; ', $issues )
            ),
            'severity'      => 'medium',
            'threat_level'  => 25,
            'auto_fixable'  => false,
            'kb_link'       => 'https://wpshadow.com/kb/feed-validity',
            'manual_steps'  => array(
                __( 'Check your theme\'s functions.php for any output before the closing PHP tag', 'wpshadow' ),
                __( 'Disable plugins one by one to identify conflicts', 'wpshadow' ),
                __( 'Validate your feed at https://validator.w3.org/feed/', 'wpshadow' ),
            ),
            'impact'        => array(
                'seo' => __( 'Feed readers and RSS aggregators cannot access your content', 'wpshadow' ),
            ),
            'evidence'      => array(
                'rss_url'  => $rss_url,
                'atom_url' => $atom_url,
                'issues'   => $issues,
            ),
        );
    }
}
