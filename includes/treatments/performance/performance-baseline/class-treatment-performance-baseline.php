<?php
/**
 * Performance Baseline Treatment
 *
 * Checks if initial performance metrics have been recorded for comparison.
 *
 * @package    WPShadow
 * @subpackage Treatments/Performance
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments\Performance;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Performance Baseline Treatment Class
 *
 * Validates that baseline performance metrics have been recorded
 * for tracking performance improvements over time.
 *
 * @since 1.6050.0000
 */
class Treatment_Performance_Baseline extends Treatment_Base {

    /**
     * The treatment slug
     *
     * @var string
     */
    protected static $slug = 'performance-baseline';

    /**
     * The treatment title
     *
     * @var string
     */
    protected static $title = 'Performance Baseline';

    /**
     * The treatment description
     *
     * @var string
     */
    protected static $description = 'Initial performance metrics recorded';

    /**
     * The family this treatment belongs to
     *
     * @var string
     */
    protected static $family = 'performance';

    /**
     * Run the treatment check.
     *
     * @since  1.6050.0000
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if WPShadow has recorded baseline metrics
        $baseline = get_option( 'wpshadow_performance_baseline' );

        if ( ! $baseline ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'No performance baseline recorded. Record metrics to track improvements.', 'wpshadow' ),
                'severity'      => 'low',
                'threat_level'  => 20,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/performance-baseline',
                'persona'       => 'developer',
            );
        }

        // Check if baseline is recent (within 30 days)
        $baseline_time = (int) $baseline['timestamp'] ?? 0;
        $current_time  = time();
        $age_days      = floor( ( $current_time - $baseline_time ) / 86400 );

        if ( $age_days > 30 ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: days */
                    __( 'Performance baseline is %d days old. Consider refreshing for current metrics.', 'wpshadow' ),
                    $age_days
                ),
                'severity'      => 'low',
                'threat_level'  => 15,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/performance-baseline',
                'persona'       => 'developer',
            );
        }

        return null; // No issue found
    }
}
