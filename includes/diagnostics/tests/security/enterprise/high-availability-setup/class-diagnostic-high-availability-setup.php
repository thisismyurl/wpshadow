<?php
/**
 * High Availability Setup Diagnostic
 *
 * Checks if load balancing and high availability are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Enterprise
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Enterprise;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * High Availability Setup Diagnostic Class
 *
 * Validates that high availability infrastructure is configured with load balancing.
 *
 * @since 1.6093.1200
 */
class Diagnostic_High_Availability_Setup extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'high-availability-setup';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'High Availability Setup';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Load balancing configured';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'enterprise';

    /**
     * Run the diagnostic check.
     *
     * @since 1.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check for load balancer configuration
        $lb_configured = get_option( 'wpshadow_load_balancer_enabled' );

        if ( ! $lb_configured ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'No load balancer configured. High availability requires load distribution across servers.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 65,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/high-availability-setup',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check for multiple backends
        $backends = get_option( 'wpshadow_load_balancer_backends' );

        if ( ! $backends || count( (array) $backends ) < 2 ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Only one backend server configured. Add multiple servers for redundancy.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 60,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/high-availability-setup',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}
