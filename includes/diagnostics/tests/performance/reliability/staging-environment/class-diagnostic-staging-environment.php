<?php
/**
 * Staging Environment Diagnostic
 *
 * Checks if a staging environment mirrors production configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Reliability
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Reliability;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Staging Environment Diagnostic Class
 *
 * Validates that staging environment is properly configured to mirror production.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Staging_Environment extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'staging-environment';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Staging Environment';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Staging mirrors production';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'reliability';

    /**
     * Run the diagnostic check.
     *
     * @since  1.6050.0000
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if staging environment is configured
        $staging_url = get_option( 'wpshadow_staging_url' );

        if ( ! $staging_url ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'No staging environment configured. Create a staging site for safe testing.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 50,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/staging-environment',
                'persona'       => 'developer',
            );
        }

        // Check if staging is regularly synced with production
        $last_sync = get_option( 'wpshadow_staging_last_sync' );

        if ( ! $last_sync ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Staging environment never synced with production. Sync for accurate testing.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 45,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/staging-environment',
                'persona'       => 'developer',
            );
        }

        $last_sync_time = (int) $last_sync;
        $current_time   = time();
        $days_since_sync = floor( ( $current_time - $last_sync_time ) / 86400 );

        if ( $days_since_sync > 7 ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: days */
                    __( 'Staging not synced in %d days. Sync to keep it current with production.', 'wpshadow' ),
                    $days_since_sync
                ),
                'severity'      => 'low',
                'threat_level'  => 25,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/staging-environment',
                'persona'       => 'developer',
            );
        }

        return null; // No issue found
    }
}
