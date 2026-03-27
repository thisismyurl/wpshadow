<?php
/**
 * Security Headers Treatment
 *
 * Checks if important security headers are properly configured.
 *
 * @package    WPShadow
 * @subpackage Treatments/Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments\Security;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Security Headers Treatment Class
 *
 * Validates security headers including X-Content-Type-Options, X-Frame-Options,
 * Content-Security-Policy, etc.
 *
 * @since 1.6093.1200
 */
class Treatment_Security_Headers extends Treatment_Base {

    /**
     * The treatment slug
     *
     * @var string
     */
    protected static $slug = 'security-headers';

    /**
     * The treatment title
     *
     * @var string
     */
    protected static $title = 'Security Headers';

    /**
     * The treatment description
     *
     * @var string
     */
    protected static $description = 'Security headers properly configured';

    /**
     * The family this treatment belongs to
     *
     * @var string
     */
    protected static $family = 'security';

    /**
     * Run the treatment check.
     *
     * @since 1.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
    	return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Security_Headers' );
    }
}
