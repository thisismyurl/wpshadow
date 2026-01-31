<?php
/**
 * Diagnostic: Database Corruption Check
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
 * Diagnostic_DatabaseCorruptionCheck Class
 */
class Diagnostic_DatabaseCorruptionCheck extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'database-corruption-check';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Database Corruption Check';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Detect if WordPress database tables are corrupted';

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
        
        // Key tables to check
        $tables_to_check = array(
            $wpdb->posts,
            $wpdb->postmeta,
            $wpdb->users,
            $wpdb->usermeta,
            $wpdb->options,
            $wpdb->comments,
            $wpdb->commentmeta,
            $wpdb->terms,
            $wpdb->termmeta,
            $wpdb->term_taxonomy,
            $wpdb->term_relationships,
        );
        
        $corrupted_tables = array();
        $warnings = array();
        
        foreach ( $tables_to_check as $table ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $check_result = $wpdb->get_results( "CHECK TABLE `{$table}`", ARRAY_A );
            
            if ( ! empty( $check_result ) ) {
                foreach ( $check_result as $row ) {
                    $msg_type = strtolower( $row['Msg_type'] ?? '' );
                    $msg_text = $row['Msg_text'] ?? '';
                    
                    if ( 'error' === $msg_type || 'warning' === $msg_type ) {
                        if ( 'error' === $msg_type ) {
                            $corrupted_tables[] = sprintf(
                                /* translators: 1: table name, 2: error message */
                                __( '%1$s: %2$s', 'wpshadow' ),
                                $table,
                                $msg_text
                            );
                        } else {
                            $warnings[] = sprintf(
                                /* translators: 1: table name, 2: warning message */
                                __( '%1$s: %2$s', 'wpshadow' ),
                                $table,
                                $msg_text
                            );
                        }
                    }
                }
            }
        }
        
        if ( empty( $corrupted_tables ) && empty( $warnings ) ) {
            return null; // All tables are healthy
        }
        
        $threat_level = 80; // Critical if corrupted
        
        if ( empty( $corrupted_tables ) && ! empty( $warnings ) ) {
            $threat_level = 50; // Medium if only warnings
        }
        
        $description_parts = array();
        
        if ( ! empty( $corrupted_tables ) ) {
            $description_parts[] = sprintf(
                /* translators: %s: list of corrupted tables */
                __( 'CRITICAL: Corrupted tables detected: %s', 'wpshadow' ),
                implode( ', ', $corrupted_tables )
            );
        }
        
        if ( ! empty( $warnings ) ) {
            $description_parts[] = sprintf(
                /* translators: %s: list of warnings */
                __( 'Warnings found: %s', 'wpshadow' ),
                implode( ', ', $warnings )
            );
        }
        
        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => implode( '. ', $description_parts ) . '. ' . __( 'Database corruption can cause site crashes and data loss.', 'wpshadow' ),
            'severity'      => $threat_level >= 70 ? 'critical' : 'medium',
            'threat_level'  => $threat_level,
            'auto_fixable'  => true,
            'kb_link'       => 'https://wpshadow.com/kb/database-corruption-check',
            'manual_steps'  => array(
                __( 'Backup your database immediately', 'wpshadow' ),
                __( 'Run REPAIR TABLE command for corrupted tables', 'wpshadow' ),
                __( 'Contact hosting provider if issue persists', 'wpshadow' ),
                __( 'Check server logs for hardware issues', 'wpshadow' ),
            ),
            'impact'        => array(
                'data'        => __( 'Risk of data loss or corruption spreading', 'wpshadow' ),
                'performance' => __( 'Corrupted tables significantly slow queries', 'wpshadow' ),
                'stability'   => __( 'Site may crash or become unavailable', 'wpshadow' ),
            ),
            'evidence'      => array(
                'corrupted_tables' => $corrupted_tables,
                'warnings'         => $warnings,
                'tables_checked'   => count( $tables_to_check ),
            ),
        );
    }
}
