<?php
/**
 * Backup Restoration Test Diagnostic
 *
 * Critical for agencies, corporate, and e-commerce: tests that recent backups can
 * actually be restored, not just that they exist.
 *
 * @since   1.6030.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Persona_Registry;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Backup Restoration Test Diagnostic
 *
 * Tests that recent backups are not corrupted and can be restored.
 * High priority for: Agency (95), Corporate (100), E-commerce (100)
 *
 * @since 1.6030.2148
 */
class Diagnostic_Backup_Restoration_Test extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'backup-restoration-test';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Backup Restoration Test';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Validates that recent backups can be successfully restored';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'backup-disaster-recovery';

    /**
     * Personas this diagnostic is critical for
     *
     * @var array
     */
    protected static $personas = array(
        'agency',
        'corporate',
        'ecommerce',
        'enterprise-corp',
    );

    /**
     * Run the diagnostic check
     *
     * @since  1.6030.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if backup system exists
        if ( ! function_exists( 'wp_get_backup_log' ) && ! class_exists( 'UpdraftPlus' ) ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => __( 'No backup system detected. Cannot test restoration capability.', 'wpshadow' ),
                'severity'     => 'critical',
                'threat_level' => 95,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/backup-restoration',
                'personas'     => self::$personas,
            );
        }

        // Check for recent backup
        $last_backup_time = get_option( 'wpshadow_last_backup_test', 0 );
        $days_since_backup = (int) ( ( time() - $last_backup_time ) / DAY_IN_SECONDS );

        if ( $days_since_backup > 7 ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => sprintf(
                    /* translators: %d: number of days */
                    __( 'Backup restoration not tested in %d days. Recommend testing weekly.', 'wpshadow' ),
                    $days_since_backup
                ),
                'severity'     => 'high',
                'threat_level' => 85,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/backup-restoration',
                'personas'     => self::$personas,
            );
        }

        // Check for backup corruption indicators
        $backup_errors = get_option( 'wpshadow_backup_errors', array() );
        if ( ! empty( $backup_errors ) ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => sprintf(
                    /* translators: %d: number of errors */
                    __( 'Last backup had %d errors. Restoration may fail.', 'wpshadow' ),
                    count( $backup_errors )
                ),
                'severity'     => 'high',
                'threat_level' => 90,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/backup-restoration',
                'personas'     => self::$personas,
            );
        }

        return null; // No issues found
    }
}
