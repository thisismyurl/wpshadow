<?php
/**
 * Core Web Vitals Score Diagnostic
 *
 * Critical for publishers: measures Google Core Web Vitals (LCP, FID, CLS)
 * which directly impact SEO rankings and audience reach.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Core Web Vitals Score Diagnostic
 *
 * Measures Core Web Vitals: Largest Contentful Paint (LCP), First Input Delay (FID),
 * and Cumulative Layout Shift (CLS). High priority for publishers focused on SEO.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Core_Web_Vitals_Score extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'core-web-vitals-score';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Google Core Web Vitals';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Measures LCP, FID, and CLS - metrics Google uses for ranking';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'seo-performance';

    /**
     * Personas this diagnostic is critical for
     *
     * @var array
     */
    protected static $personas = array(
        'publisher',
        'agency',
        'developer',
        'ecommerce',
    );

    /**
     * Run the diagnostic check
     *
     * @since 1.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Get cached Core Web Vitals from Google PageSpeed Insights API
        $cache_key = 'wpshadow_cwv_score';
        $cwv_data = get_transient( $cache_key );

        if ( false === $cwv_data ) {
            // Fetch from PageSpeed Insights if not cached
            $cwv_data = self::fetch_cwv_from_pagespeed_insights();
            if ( ! $cwv_data ) {
                return array(
                    'id'           => self::$slug,
                    'title'        => self::$title,
                    'description'  => __( 'Could not fetch Core Web Vitals data. Check homepage loads properly.', 'wpshadow' ),
                    'severity'     => 'medium',
                    'threat_level' => 40,
                    'auto_fixable' => false,
                    'kb_link'      => 'https://wpshadow.com/kb/core-web-vitals',
                    'personas'     => self::$personas,
                );
            }
            set_transient( $cache_key, $cwv_data, HOUR_IN_SECONDS );
        }

        // Extract metrics
        $lcp = $cwv_data['lcp'] ?? null;    // Largest Contentful Paint (should be < 2.5s)
        $fid = $cwv_data['fid'] ?? null;    // First Input Delay (should be < 100ms)
        $cls = $cwv_data['cls'] ?? null;    // Cumulative Layout Shift (should be < 0.1)

        $failed_metrics = array();

        if ( $lcp && $lcp > 2500 ) {
            $failed_metrics[] = sprintf(
                /* translators: %d: milliseconds */
                __( 'LCP: %dms (target: <2500ms)', 'wpshadow' ),
                $lcp
            );
        }

        if ( $fid && $fid > 100 ) {
            $failed_metrics[] = sprintf(
                /* translators: %d: milliseconds */
                __( 'FID: %dms (target: <100ms)', 'wpshadow' ),
                $fid
            );
        }

        if ( $cls && $cls > 0.1 ) {
            $failed_metrics[] = sprintf(
                /* translators: %f: layout shift score */
                __( 'CLS: %.2f (target: <0.1)', 'wpshadow' ),
                $cls
            );
        }

        if ( ! empty( $failed_metrics ) ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => sprintf(
                    /* translators: %s: list of failed metrics */
                    __( 'Core Web Vitals issues: %s. These impact SEO rankings and user experience.', 'wpshadow' ),
                    implode( ', ', $failed_metrics )
                ),
                'severity'     => count( $failed_metrics ) > 1 ? 'critical' : 'high',
                'threat_level' => 75 + ( 10 * count( $failed_metrics ) ),
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/core-web-vitals',
                'personas'     => self::$personas,
                'seo_impact'   => __( 'Poor Core Web Vitals can reduce organic search ranking by 1-3 positions', 'wpshadow' ),
            );
        }

        return null; // All metrics pass
    }

    /**
     * Fetch Core Web Vitals from Google PageSpeed Insights API
     *
     * @since 1.6093.1200
     * @return array|false Array with LCP, FID, CLS, or false on error.
     */
    private static function fetch_cwv_from_pagespeed_insights() {
        $home_url = home_url();
        $api_key = get_option( 'wpshadow_pagespeed_api_key', '' );

        if ( ! $api_key ) {
            return false;
        }

        $url = add_query_arg(
            array(
                'url' => $home_url,
                'key' => $api_key,
            ),
            'https://www.googleapis.com/pagespeedonline/v5/runPagespeed'
        );

        $response = wp_remote_get( $url, array( 'timeout' => 30 ) );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( ! $data ) {
            return false;
        }

        // Extract metrics from response
        $metrics = $data['lighthouseResult']['audits']['metrics']['details']['items'][0] ?? array();

        return array(
            'lcp' => $metrics['largest_contentful_paint'] ?? null,
            'fid' => $metrics['first_input_delay'] ?? null,
            'cls' => $metrics['cumulative_layout_shift'] ?? null,
        );
    }
}
