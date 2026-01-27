<?php
/**
 * Diagnostic: Robots.txt availability
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
 * Diagnostic_RobotstxtAvailability Class
 */
class Diagnostic_RobotstxtAvailability extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'robotstxt-availability';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Robots.txt availability';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Confirm robots.txt accessible and valid';

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
        $robots_url = home_url( '/robots.txt' );

        // Test robots.txt availability
        $response = wp_remote_get( $robots_url, array(
            'timeout' => 10,
            'redirection' => 5,
        ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %s: error message */
                    __( 'Your robots.txt file is not accessible: %s. This file tells search engines which pages they can crawl.', 'wpshadow' ),
                    $response->get_error_message()
                ),
                'severity'      => 'medium',
                'threat_level'  => 25,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/robotstxt-availability',
                'manual_steps'  => array(
                    __( 'Check if robots.txt is blocked in server configuration', 'wpshadow' ),
                    __( 'Verify file permissions if physical robots.txt exists', 'wpshadow' ),
                    __( 'WordPress generates virtual robots.txt by default', 'wpshadow' ),
                ),
                'impact'        => array(
                    'seo' => __( 'Search engines cannot read crawl instructions', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'robots_url' => $robots_url,
                    'error'      => $response->get_error_message(),
                ),
            );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        
        if ( 200 !== $response_code ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: HTTP response code */
                    __( 'Your robots.txt returns HTTP %d instead of 200. Search engines expect a valid robots.txt file.', 'wpshadow' ),
                    $response_code
                ),
                'severity'      => 'medium',
                'threat_level'  => 25,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/robotstxt-availability',
                'manual_steps'  => array(
                    __( 'Check server redirect rules', 'wpshadow' ),
                    __( 'Verify SSL certificate is valid', 'wpshadow' ),
                    __( 'Test the URL directly in your browser', 'wpshadow' ),
                ),
                'impact'        => array(
                    'seo' => __( 'Search engines may not properly crawl your site', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'robots_url'    => $robots_url,
                    'response_code' => $response_code,
                ),
            );
        }

        $body = wp_remote_retrieve_body( $response );
        
        // Check for common robots.txt issues
        $issues = array();

        // Check if robots.txt is blocking everything
        if ( preg_match( '/User-agent:\s*\*\s+Disallow:\s*\//i', $body ) ) {
            $issues[] = __( 'Robots.txt is blocking all search engines from crawling your site', 'wpshadow' );
        }

        // Check if robots.txt blocks important directories
        if ( preg_match( '/Disallow:\s*\/wp-content/i', $body ) ) {
            $issues[] = __( 'Robots.txt blocks /wp-content/ which may prevent indexing of images and media', 'wpshadow' );
        }

        // Check if robots.txt is empty or too short (likely not configured)
        if ( strlen( trim( $body ) ) < 20 ) {
            $issues[] = __( 'Robots.txt appears to be minimal or unconfigured', 'wpshadow' );
        }

        // Check for sitemap reference
        $has_sitemap = preg_match( '/Sitemap:/i', $body );

        if ( ! $has_sitemap ) {
            $issues[] = __( 'Robots.txt does not reference your sitemap', 'wpshadow' );
        }

        // If there are configuration issues, report them
        if ( ! empty( $issues ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %s: list of issues */
                    __( 'Your robots.txt has configuration issues: %s', 'wpshadow' ),
                    implode( '; ', $issues )
                ),
                'severity'      => 'medium',
                'threat_level'  => 25,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/robotstxt-availability',
                'manual_steps'  => array(
                    __( 'Review your robots.txt configuration', 'wpshadow' ),
                    __( 'Add sitemap reference: Sitemap: ' . home_url( '/sitemap_index.xml' ), 'wpshadow' ),
                    __( 'Avoid blocking important content directories', 'wpshadow' ),
                    __( 'Test your robots.txt with Google Search Console', 'wpshadow' ),
                ),
                'impact'        => array(
                    'seo' => __( 'Search engines may not properly index your content', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'robots_url'    => $robots_url,
                    'content'       => $body,
                    'issues'        => $issues,
                    'has_sitemap'   => $has_sitemap,
                    'content_length' => strlen( $body ),
                ),
            );
        }

        // robots.txt is accessible and properly configured
        return null;
    }
}
