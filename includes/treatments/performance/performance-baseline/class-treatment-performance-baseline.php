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
    	return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Performance\Diagnostic_Performance_Baseline' );
    }
}
