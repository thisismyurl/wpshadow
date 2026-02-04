<?php
/**
 * DDoS Mitigation Diagnostic
 *
 * Checks if DDoS protection service is active and configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Security
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * DDoS Mitigation Diagnostic Class
 *
 * Validates that DDoS protection service is active and monitoring traffic.
 *
 * @since 1.6050.0000
 */
class Diagnostic_DDoS_Mitigation extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'ddos-mitigation';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'DDoS Mitigation';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'DDoS protection service active';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'security';

    /**
     * Run the diagnostic check.
     *
     * @since  1.6050.0000
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if DDoS protection is enabled
        $ddos_enabled = get_option( 'wpshadow_ddos_protection_enabled' );

        if ( ! $ddos_enabled ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'DDoS protection not enabled. Enable for protection against distributed denial of service attacks.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 75,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/ddos-mitigation',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if service provider is reachable
        $service_reachable = get_option( 'wpshadow_ddos_service_reachable' );

        if ( ! $service_reachable ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'DDoS protection service unreachable. Check service status and network connectivity.', 'wpshadow' ),
                'severity'      => 'critical',
                'threat_level'  => 90,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/ddos-mitigation',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if currently under attack
        $under_attack = get_option( 'wpshadow_site_under_ddos_attack' );

        if ( $under_attack ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Site currently under DDoS attack. Monitor protection service.', 'wpshadow' ),
                'severity'      => 'critical',
                'threat_level'  => 95,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/ddos-mitigation',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}
