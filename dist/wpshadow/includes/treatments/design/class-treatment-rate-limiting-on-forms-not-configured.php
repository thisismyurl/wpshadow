<?php
/**
 * Rate Limiting On Forms Not Configured Treatment
 *
 * Checks form rate limiting.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Rate_Limiting_On_Forms_Not_Configured Class
 *
 * Performs treatment check for Rate Limiting On Forms Not Configured.
 *
 * @since 1.6093.1200
 */
class Treatment_Rate_Limiting_On_Forms_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'rate-limiting-on-forms-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Rate Limiting On Forms Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks form rate limiting';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Rate_Limiting_On_Forms_Not_Configured' );
	}
						return null;
						}
						return null;
	}
}
