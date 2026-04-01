<?php
/**
 * LDAP/Active Directory Diagnostic
 *
 * Checks if directory integration is active and properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Enterprise
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Enterprise;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * LDAP/Active Directory Diagnostic Class
 *
 * Validates that LDAP/Active Directory integration is properly configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_LDAP_Active_Directory extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'ldap-active-directory';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'LDAP/Active Directory';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Directory integration active';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'enterprise';

    /**
     * Run the diagnostic check.
     *
     * @since 0.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if LDAP extension is loaded
        if ( ! extension_loaded( 'ldap' ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Your server needs the LDAP extension to connect to company directory services (like a digital employee phone book). This lets people use the same login they use for email and other work tools. Your hosting provider can enable this.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 60,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/ldap-active-directory?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if LDAP/AD is configured
        $ldap_enabled = get_option( 'wpshadow_ldap_enabled' );

        if ( ! $ldap_enabled ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Connecting to your company directory (LDAP/Active Directory) lets employees use their work login credentials for WordPress. Think of it like "Sign in with your work account" instead of creating yet another username and password.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 40,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/ldap-active-directory?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check connection status
        $ldap_connected = get_option( 'wpshadow_ldap_connection_status' );

        if ( 'connected' !== $ldap_connected ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'LDAP connection failed. Check server address and credentials.', 'wpshadow' ),
                'severity'      => 'critical',
                'threat_level'  => 80,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/ldap-active-directory?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}
