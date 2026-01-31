<?php
/**
 * Diagnostic: Database Optimization
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
 * Diagnostic_DatabaseOptimization Class
 */
class Diagnostic_DatabaseOptimization extends Diagnostic_Base {

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
    protected static $description = 'Detect if WordPress database tables are fragmented and need optimization';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'database';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        global $wpdb;
        
        // Get table status information
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $tables = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT TABLE_NAME, DATA_LENGTH, DATA_FREE, ENGINE
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = %s
                AND TABLE_NAME LIKE %s",
                DB_NAME,
                $wpdb->esc_like( $wpdb->prefix ) . '%'
            ),
            ARRAY_A
        );
        
        if ( empty( $tables ) ) {
            return null; // Can't check
        }
        
        $fragmented_tables = array();
        $total_data_free = 0;
        
        foreach ( $tables as $table ) {
            $table_name = $table['TABLE_NAME'];
            $data_length = (int) $table['DATA_LENGTH'];
            $data_free = (int) $table['DATA_FREE'];
            $engine = $table['ENGINE'];
            
            // Only check InnoDB and MyISAM tables
            if ( ! in_array( $engine, array( 'InnoDB', 'MyISAM' ), true ) ) {
                continue;
            }
            
            // Skip if no data
            if ( $data_length === 0 ) {
                continue;
            }
            
            // Calculate fragmentation percentage
            $fragmentation_pct = ( $data_free / ( $data_length + $data_free ) ) * 100;
            
            // Flag if fragmented > 20%
            if ( $fragmentation_pct > 20 ) {
                $fragmented_tables[] = array(
                    'name'              => $table_name,
                    'fragmentation_pct' => round( $fragmentation_pct, 2 ),
                    'data_free'         => size_format( $data_free ),
                );
                $total_data_free += $data_free;
            }
        }
        
        if ( empty( $fragmented_tables ) ) {
            return null; // Tables are optimized
        }
        
        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => sprintf(
                /* translators: 1: number of tables, 2: total wasted space */
                __( '%1$d database tables are fragmented, wasting %2$s of space. Optimization can improve query performance.', 'wpshadow' ),
                count( $fragmented_tables ),
                size_format( $total_data_free )
            ),
            'severity'      => 'medium',
            'threat_level'  => 40,
            'auto_fixable'  => true,
            'kb_link'       => 'https://wpshadow.com/kb/database-optimization',
            'manual_steps'  => array(
                __( 'Run OPTIMIZE TABLE on fragmented tables', 'wpshadow' ),
                __( 'Schedule optimization during low-traffic periods', 'wpshadow' ),
                __( 'Consider automated optimization plugins', 'wpshadow' ),
            ),
            'impact'        => array(
                'performance' => __( 'Fragmented tables slow down queries', 'wpshadow' ),
                'storage'     => __( 'Wasted disk space can be recovered', 'wpshadow' ),
            ),
            'evidence'      => array(
                'fragmented_tables' => $fragmented_tables,
                'total_data_free'   => $total_data_free,
                'tables_checked'    => count( $tables ),
            ),
        );
    }
}
