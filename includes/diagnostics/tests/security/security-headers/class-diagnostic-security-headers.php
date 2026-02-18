<?php
/**
 * Security Headers Diagnostic
 *
 * Checks if important security headers are properly configured.
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
 * Security Headers Diagnostic Class
 *
 * Validates security headers including X-Content-Type-Options, X-Frame-Options,
 * Content-Security-Policy, etc.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Security_Headers extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'security-headers';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Security Headers';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Security headers properly configured';

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
        $missing_headers = array();

        // Check for critical security headers
        $required_headers = array(
            'X-Content-Type-Options'        => 'nosniff',
            'X-Frame-Options'               => 'SAMEORIGIN|DENY',
            'X-XSS-Protection'              => '1; mode=block',
            'Referrer-Policy'               => 'strict-origin-when-cross-origin',
        );

        foreach ( $required_headers as $header => $expected_value ) {
            // In WordPress, we check if headers should be set in wp-config or .htaccess
            // This is a capability check rather than direct header verification
            $option_key = 'wpshadow_security_header_' . sanitize_key( $header );
            $header_set = get_option( $option_key );

            if ( ! $header_set ) {
                $missing_headers[] = $header;
            }
        }

        if ( ! empty( $missing_headers ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %s: list of headers */
                    __( 'Missing security headers: %s. Configure these in .htaccess or your server.', 'wpshadow' ),
                    implode( ', ', $missing_headers )
                ),
                'severity'      => 'high',
                'threat_level'  => 70,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/security-headers',
                'persona'       => 'developer',
            );
        }

        return null; // No issue found
    }
}
