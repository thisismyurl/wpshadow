<?php
/**
 * Checkout Speed Diagnostic
 *
 * Critical for e-commerce: measures checkout page load time.
 * Slow checkouts directly impact conversion rate and revenue.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Checkout Speed Diagnostic
 *
 * Measures E-commerce checkout page load time. Targets <2 second load time.
 * High priority for: E-commerce (100)
 *
 * @since 0.6093.1200
 */
class Diagnostic_Checkout_Speed extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'checkout-speed';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Checkout Page Load Speed';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Measures checkout page load time and identifies slowdowns';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'performance';

    /**
     * Personas this diagnostic is critical for
     *
     * @var array
     */
    protected static $personas = array(
        'ecommerce',
        'agency',
        'developer',
    );

    /**
     * Run the diagnostic check
     *
     * @since 0.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            return null; // Not applicable to non-ecommerce sites
        }

        // Get checkout page ID
        $checkout_page = wc_get_checkout_url();
        if ( ! $checkout_page ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => __( 'Checkout page not found. Configure in WooCommerce settings.', 'wpshadow' ),
                'severity'     => 'critical',
                'threat_level' => 100,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/ecommerce-checkout-speed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'personas'     => self::$personas,
            );
        }

        // Measure checkout page load time
        $load_time = self::measure_page_load_time( $checkout_page );

        // Thresholds: <2 seconds is good, 2-3 is acceptable, >3 is poor
        if ( $load_time > 3000 ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => sprintf(
                    /* translators: %d: milliseconds */
                    __( 'Checkout page loads in %dms (target: <2000ms). Every 100ms adds ~1%% cart abandonment.', 'wpshadow' ),
                    $load_time
                ),
                'severity'     => 'critical',
                'threat_level' => 95,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/ecommerce-checkout-speed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'personas'     => self::$personas,
                'impact'       => sprintf(
                    /* translators: %d: estimated abandonment rate */
                    __( 'Estimated revenue loss: ~%d%% of transactions', 'wpshadow' ),
                    ( $load_time - 2000 ) / 100
                ),
            );
        } elseif ( $load_time > 2000 ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => sprintf(
                    /* translators: %d: milliseconds */
                    __( 'Checkout loads in %dms. Consider optimizing to <2000ms for best conversion.', 'wpshadow' ),
                    $load_time
                ),
                'severity'     => 'high',
                'threat_level' => 60,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/ecommerce-checkout-speed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'personas'     => self::$personas,
            );
        }

        return null; // Performance is good
    }

    /**
     * Measure page load time using HTTP HEAD request
     *
     * @since 0.6093.1200
     * @param  string $url Page URL to measure.
     * @return int Load time in milliseconds.
     */
    private static function measure_page_load_time( $url ) {
        $start = microtime( true );

        $response = wp_remote_head(
            $url,
            array(
                'timeout'   => 10,
                'sslverify' => false,
            )
        );

        $end = microtime( true );
        $duration = ( $end - $start ) * 1000; // Convert to milliseconds

        return (int) $duration;
    }
}
