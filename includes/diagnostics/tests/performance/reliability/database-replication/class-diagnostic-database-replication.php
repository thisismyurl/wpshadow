<?php
/**
 * Database Replication Diagnostic
 *
 * Checks if master-slave database replication is active and configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Reliability
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Reliability;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Database Replication Diagnostic Class
 *
 * Validates that database replication is properly configured for redundancy.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Database_Replication extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'database-replication';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Database Replication';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Master-slave replication active';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'reliability';

    /**
     * Run the diagnostic check.
     *
     * @since 1.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        global $wpdb;

        // Check for replication configuration
        $replication_enabled = get_option( 'wpshadow_database_replication_enabled' );

        if ( ! $replication_enabled ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Database replication not configured. Enable for high availability and disaster recovery.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 70,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/database-replication',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check replication status
        $slave_status = get_option( 'wpshadow_slave_replication_status' );

        if ( 'running' !== $slave_status ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Database replication is not running. Check slave server status.', 'wpshadow' ),
                'severity'      => 'critical',
                'threat_level'  => 90,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/database-replication',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check for replication lag
        $replication_lag = (int) get_option( 'wpshadow_replication_lag_seconds' ) ?? 0;

        if ( $replication_lag > 10 ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: seconds */
                    __( 'Database replication lag detected (%d seconds). Check for network issues.', 'wpshadow' ),
                    $replication_lag
                ),
                'severity'      => 'medium',
                'threat_level'  => 50,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/database-replication',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}
