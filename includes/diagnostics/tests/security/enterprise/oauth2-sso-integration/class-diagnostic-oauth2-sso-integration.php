<?php
/**
 * OAuth2/SSO Integration Diagnostic
 *
 * Checks if enterprise single sign-on is properly configured.
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
 * OAuth2/SSO Integration Diagnostic Class
 *
 * Validates that enterprise single sign-on (OAuth2/SSO) is properly configured.
 *
 * @since 1.6093.1200
 */
class Diagnostic_OAuth2_SSO_Integration extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'oauth2-sso-integration';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'OAuth2/SSO Integration';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Enterprise single sign-on working';

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
        // Check if OAuth2/SSO is configured
        $sso_enabled = get_option( 'wpshadow_sso_enabled' );

        if ( ! $sso_enabled ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'OAuth2/SSO not configured. Enterprise authentication requires centralized identity management.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 45,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/oauth2-sso-integration',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check for provider configuration
        $provider = get_option( 'wpshadow_sso_provider' );

        if ( empty( $provider ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'SSO provider not configured. Configure your OAuth2 provider (Azure AD, Okta, etc).', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 55,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/oauth2-sso-integration',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if provider is reachable
        $provider_reachable = get_option( 'wpshadow_sso_provider_reachable' );

        if ( ! $provider_reachable ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'SSO provider unreachable. Check network connectivity and provider status.', 'wpshadow' ),
                'severity'      => 'critical',
                'threat_level'  => 85,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/oauth2-sso-integration',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}
