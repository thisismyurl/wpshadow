<?php
/**
 * API Rate Limiting Diagnostic
 *
 * Checks if API rate limiting is configured to protect against abuse.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * API Rate Limiting Diagnostic Class
 *
 * Validates that API rate limiting is properly configured for abuse prevention.
 *
 * @since 0.6093.1200
 */
class Diagnostic_API_Rate_Limiting extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'api-rate-limiting';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'API Rate Limiting';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'API protection against abuse';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'security';

    /**
     * Run the diagnostic check.
     *
     * @since 0.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if rate limiting is enabled on REST API
        $rate_limit_enabled = get_option( 'wpshadow_api_rate_limit_enabled' );

        if ( ! $rate_limit_enabled ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'API rate limiting is not enabled. Configure to prevent abuse and DDoS attacks.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 65,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/api-rate-limiting?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check configured rate limits
        $requests_per_minute = (int) get_option( 'wpshadow_api_rate_limit_requests' ) ?? 0;

        if ( $requests_per_minute === 0 || $requests_per_minute > 10000 ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: requests per minute */
                    __( 'API rate limit too high (%d requests/min). Lower to prevent abuse.', 'wpshadow' ),
                    $requests_per_minute
                ),
                'severity'      => 'medium',
                'threat_level'  => 50,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/api-rate-limiting?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}
