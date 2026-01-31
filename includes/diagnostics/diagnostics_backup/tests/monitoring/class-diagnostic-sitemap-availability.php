<?php
/**
 * Diagnostic: Sitemap availability
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
 * Diagnostic_SitemapAvailability Class
 */
class Diagnostic_SitemapAvailability extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'sitemap-availability';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Sitemap availability';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Confirm core or plugin sitemap loads';

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
        $sitemap_urls = array();
        $available_sitemaps = array();
        $unavailable_sitemaps = array();

        // Check WordPress core sitemap (WP 5.5+)
        if ( function_exists( 'wp_sitemaps_get_server' ) ) {
            $sitemap_url = home_url( '/wp-sitemap.xml' );
            $sitemap_urls['core'] = $sitemap_url;

            $response = wp_remote_head( $sitemap_url, array(
                'timeout' => 10,
                'redirection' => 5,
            ) );

            if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                $available_sitemaps[] = __( 'WordPress core sitemap', 'wpshadow' );
            } else {
                $unavailable_sitemaps['core'] = is_wp_error( $response ) ? 
                    $response->get_error_message() : 
                    sprintf( __( 'HTTP %d', 'wpshadow' ), wp_remote_retrieve_response_code( $response ) );
            }
        }

        // Check for Yoast SEO sitemap
        if ( defined( 'WPSEO_VERSION' ) ) {
            $yoast_sitemap = home_url( '/sitemap_index.xml' );
            $sitemap_urls['yoast'] = $yoast_sitemap;

            $response = wp_remote_head( $yoast_sitemap, array(
                'timeout' => 10,
                'redirection' => 5,
            ) );

            if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                $available_sitemaps[] = __( 'Yoast SEO sitemap', 'wpshadow' );
            } else {
                $unavailable_sitemaps['yoast'] = is_wp_error( $response ) ? 
                    $response->get_error_message() : 
                    sprintf( __( 'HTTP %d', 'wpshadow' ), wp_remote_retrieve_response_code( $response ) );
            }
        }

        // Check for Rank Math sitemap
        if ( defined( 'RANK_MATH_VERSION' ) ) {
            $rankmath_sitemap = home_url( '/sitemap_index.xml' );
            $sitemap_urls['rankmath'] = $rankmath_sitemap;

            $response = wp_remote_head( $rankmath_sitemap, array(
                'timeout' => 10,
                'redirection' => 5,
            ) );

            if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                $available_sitemaps[] = __( 'Rank Math sitemap', 'wpshadow' );
            } else {
                $unavailable_sitemaps['rankmath'] = is_wp_error( $response ) ? 
                    $response->get_error_message() : 
                    sprintf( __( 'HTTP %d', 'wpshadow' ), wp_remote_retrieve_response_code( $response ) );
            }
        }

        // If no sitemap solutions are installed or all are unavailable
        if ( empty( $sitemap_urls ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'No sitemap solution detected. A sitemap helps search engines discover and index your content.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 25,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/sitemap-availability',
                'manual_steps'  => array(
                    __( 'WordPress has a built-in sitemap feature (available since WP 5.5)', 'wpshadow' ),
                    __( 'Consider installing an SEO plugin like Yoast SEO or Rank Math', 'wpshadow' ),
                    __( 'Submit your sitemap to Google Search Console', 'wpshadow' ),
                ),
                'impact'        => array(
                    'seo' => __( 'Search engines may have difficulty discovering your content', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'wordpress_version' => get_bloginfo( 'version' ),
                    'has_core_sitemaps' => function_exists( 'wp_sitemaps_get_server' ),
                ),
            );
        }

        // If at least one sitemap is available, no issue
        if ( ! empty( $available_sitemaps ) ) {
            return null;
        }

        // All sitemaps are unavailable
        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => sprintf(
                /* translators: %s: list of unavailable sitemaps */
                __( 'Your sitemaps are not accessible: %s. Search engines use sitemaps to discover and index your content.', 'wpshadow' ),
                implode( ', ', array_keys( $unavailable_sitemaps ) )
            ),
            'severity'      => 'medium',
            'threat_level'  => 25,
            'auto_fixable'  => false,
            'kb_link'       => 'https://wpshadow.com/kb/sitemap-availability',
            'manual_steps'  => array(
                __( 'Check if sitemaps are blocked in robots.txt', 'wpshadow' ),
                __( 'Verify no security plugin is blocking sitemap access', 'wpshadow' ),
                __( 'Test sitemap URLs directly in your browser', 'wpshadow' ),
                __( 'Check for redirect loops or SSL certificate issues', 'wpshadow' ),
            ),
            'impact'        => array(
                'seo' => __( 'Search engines cannot efficiently index your content', 'wpshadow' ),
            ),
            'evidence'      => array(
                'sitemap_urls'         => $sitemap_urls,
                'available_sitemaps'   => $available_sitemaps,
                'unavailable_sitemaps' => $unavailable_sitemaps,
            ),
        );
    }
}
