<?php
/**
 * Error Logging Treatment
 *
 * Checks if debug logging is configured and disabled in production.
 *
 * @package    WPShadow
 * @subpackage Treatments/WordPress-Health
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments\WordPress_Health;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Error Logging Treatment Class
 *
 * Validates that debug logging is properly configured and disabled in production.
 *
 * @since 1.6093.1200
 */
class Treatment_Error_Logging extends Treatment_Base {

    /**
     * The treatment slug
     *
     * @var string
     */
    protected static $slug = 'error-logging';

    /**
     * The treatment title
     *
     * @var string
     */
    protected static $title = 'Error Logging';

    /**
     * The treatment description
     *
     * @var string
     */
    protected static $description = 'Debug logging configured but disabled in production';

    /**
     * The family this treatment belongs to
     *
     * @var string
     */
    protected static $family = 'wordpress-health';

    /**
     * Run the treatment check.
     *
     * @since 1.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
    	return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\WordPress_Health\Diagnostic_Error_Logging' );
    }
}
