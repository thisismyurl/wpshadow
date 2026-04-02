<?php
/**
 * WAF Rules Diagnostic
 *
 * Checks if web application firewall (WAF) is configured with rules.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WAF Rules Diagnostic Class
 *
 * Validates that web application firewall is properly configured with protection rules.
 *
 * @since 1.6093.1200
 */
class Diagnostic_WAF_Rules extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'waf-rules';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'WAF Rules';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Web application firewall configured';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'security';

    /**
     * Run the diagnostic check.
     *
     * @since 1.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if WAF is enabled
        $waf_enabled = get_option( 'wpshadow_waf_enabled' );

        if ( ! $waf_enabled ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Web Application Firewall not enabled. Configure WAF for attack prevention.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 70,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/waf-rules',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check for active rules
        $active_rules = (int) get_option( 'wpshadow_waf_active_rules_count' ) ?? 0;

        if ( $active_rules === 0 ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'No WAF rules enabled. Enable protection rules to defend against attacks.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 75,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/waf-rules',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if rules are up to date
        $rules_updated = get_option( 'wpshadow_waf_rules_last_updated' );

        if ( ! $rules_updated ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'WAF rules have never been updated. Regularly update for new threat patterns.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 45,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/waf-rules',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}
