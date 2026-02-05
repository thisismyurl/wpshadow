<?php
/**
 * Error Logging Treatment
 *
 * Checks if debug logging is configured and disabled in production.
 *
 * @package    WPShadow
 * @subpackage Treatments/WordPress-Health
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments\WordPress_Health;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Error Logging Treatment Class
 *
 * Validates that debug logging is properly configured and disabled in production.
 *
 * @since 1.6050.0000
 */
class Treatment_Error_Logging extends Treatment_Base {

    /**
     * The treatment slug
     *
     * @var string
     */
    protected static $slug = 'error-logging';

    /**
     * The treatment title
     *
     * @var string
     */
    protected static $title = 'Error Logging';

    /**
     * The treatment description
     *
     * @var string
     */
    protected static $description = 'Debug logging configured but disabled in production';

    /**
     * The family this treatment belongs to
     *
     * @var string
     */
    protected static $family = 'wordpress-health';

    /**
     * Run the treatment check.
     *
     * @since  1.6050.0000
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if WP_DEBUG is enabled in production
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! ( defined( 'WP_ENVIRONMENT_TYPE' ) && 'local' === WP_ENVIRONMENT_TYPE ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'WP_DEBUG is enabled in production. This exposes sensitive information.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 65,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/error-logging',
                'persona'       => 'developer',
            );
        }

        // Check if debug log exists but WP_DEBUG_LOG is enabled
        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            $log_file = WP_CONTENT_DIR . '/debug.log';

            if ( file_exists( $log_file ) ) {
                $file_size = filesize( $log_file );

                // Check if log file is growing (indicates ongoing issues)
                if ( $file_size > 1000000 ) { // > 1MB
                    return array(
                        'id'            => self::$slug,
                        'title'         => self::$title,
                        'description'   => sprintf(
                            /* translators: %s: file path */
                            __( 'Debug log is very large (%s). Ensure this is in production if expected.', 'wpshadow' ),
                            size_format( $file_size )
                        ),
                        'severity'      => 'medium',
                        'threat_level'  => 40,
                        'auto_fixable'  => false,
                        'kb_link'       => 'https://wpshadow.com/kb/error-logging',
                        'persona'       => 'developer',
                    );
                }
            }
        }

        return null; // No issue found
    }
}
