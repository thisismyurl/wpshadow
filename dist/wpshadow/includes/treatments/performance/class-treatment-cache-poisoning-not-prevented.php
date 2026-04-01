<?php
/**
 * Cache Poisoning Not Prevented Treatment
 *
 * Checks cache poisoning.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Cache_Poisoning_Not_Prevented Class
 *
 * Performs treatment check for Cache Poisoning Not Prevented.
 *
 * @since 0.6093.1200
 */
class Treatment_Cache_Poisoning_Not_Prevented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-poisoning-not-prevented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Poisoning Not Prevented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks cache poisoning';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Cache_Poisoning_Not_Prevented' );
	}
						return null;
						}
						return null;
	}
}
