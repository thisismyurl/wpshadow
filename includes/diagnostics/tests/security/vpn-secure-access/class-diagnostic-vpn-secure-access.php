<?php
/**
 * VPN/Secure Access Diagnostic
 *
 * Checks if secure remote access (VPN) is properly configured.
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
 * VPN/Secure Access Diagnostic Class
 *
 * Validates that VPN/secure access is properly configured for remote administration.
 *
 * @since 1.6050.0000
 */
class Diagnostic_VPN_Secure_Access extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'vpn-secure-access';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'VPN/Secure Access';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Secure remote access configured';

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
        // Check if VPN/secure access is configured
        $vpn_enabled = get_option( 'wpshadow_vpn_enabled' );

        if ( ! $vpn_enabled ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'VPN/secure access not configured. Enable for encrypted remote administration.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 50,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/vpn-secure-access',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check for certificate configuration
        $certificate_configured = get_option( 'wpshadow_vpn_certificate_configured' );

        if ( ! $certificate_configured ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'VPN certificate not configured. Upload certificate for encrypted access.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 65,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/vpn-secure-access',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if certificate is valid
        $certificate_valid = get_option( 'wpshadow_vpn_certificate_valid' );

        if ( ! $certificate_valid ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'VPN certificate expired or invalid. Update certificate.', 'wpshadow' ),
                'severity'      => 'critical',
                'threat_level'  => 85,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/vpn-secure-access',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}
