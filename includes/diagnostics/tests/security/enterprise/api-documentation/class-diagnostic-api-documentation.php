<?php
/**
 * API Documentation Diagnostic
 *
 * Checks if API documentation is current and accurate.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Enterprise
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Enterprise;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * API Documentation Diagnostic Class
 *
 * Validates that API documentation is current and accurate.
 *
 * @since 1.6050.0000
 */
class Diagnostic_API_Documentation extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'api-documentation';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'API Documentation';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'API docs current and accurate';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'enterprise';

    /**
     * Run the diagnostic check.
     *
     * @since  1.6050.0000
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if API documentation exists
        $api_docs_available = get_option( 'wpshadow_api_documentation_available' );

        if ( ! $api_docs_available ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'API documentation not available. Create comprehensive documentation.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 45,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/api-documentation',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if documentation is current
        $docs_last_updated = get_option( 'wpshadow_api_docs_last_updated' );

        if ( ! $docs_last_updated ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'API documentation never updated. Update docs with latest changes.', 'wpshadow' ),
                'severity'      => 'low',
                'threat_level'  => 20,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/api-documentation',
                'persona'       => 'enterprise-corp',
            );
        }

        $update_timestamp = (int) $docs_last_updated;
        $current_time     = time();
        $days_since_update = floor( ( $current_time - $update_timestamp ) / 86400 );

        if ( $days_since_update > 90 ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: days */
                    __( 'API documentation last updated %d days ago. Update for accuracy.', 'wpshadow' ),
                    $days_since_update
                ),
                'severity'      => 'low',
                'threat_level'  => 25,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/api-documentation',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}
