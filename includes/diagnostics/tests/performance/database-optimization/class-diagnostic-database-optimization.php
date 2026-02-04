<?php
/**
 * Database Optimization Diagnostic
 *
 * Checks if database indexes are created on key columns for performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Performance
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Performance;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Database Optimization Diagnostic Class
 *
 * Validates that database indexes are properly configured for performance.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Database_Optimization extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'database-optimization';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Database Optimization';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Indexes created on key columns';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'performance';

    /**
     * Run the diagnostic check.
     *
     * @since  1.6050.0000
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        global $wpdb;

        // Check for indexes on posts table key columns
        $indexes = $wpdb->get_results(
            $wpdb->prepare(
                'SHOW INDEX FROM %i WHERE Column_name IN (%s, %s, %s)',
                $wpdb->posts,
                'post_type',
                'post_status',
                'post_author'
            )
        );

        $required_indexes = array( 'post_type', 'post_status', 'post_author' );
        $missing_indexes  = array();

        foreach ( $required_indexes as $index ) {
            $found = false;
            foreach ( $indexes as $idx ) {
                if ( $index === $idx->Column_name ) {
                    $found = true;
                    break;
                }
            }
            if ( ! $found ) {
                $missing_indexes[] = $index;
            }
        }

        if ( ! empty( $missing_indexes ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %s: column names */
                    __( 'Missing database indexes on columns: %s. Add these for better query performance.', 'wpshadow' ),
                    implode( ', ', $missing_indexes )
                ),
                'severity'      => 'medium',
                'threat_level'  => 45,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/database-optimization',
                'persona'       => 'developer',
            );
        }

        return null; // No issue found
    }
}
