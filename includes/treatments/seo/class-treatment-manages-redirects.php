<?php
/**
 * Redirects Properly Managed Treatment
 *
 * Tests if old URLs are properly redirected.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirects Properly Managed Treatment Class
 *
 * Verifies that redirects are managed via plugin or server configuration.
 *
 * @since 1.6093.1200
 */
class Treatment_Manages_Redirects extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'manages-redirects';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Redirects Properly Managed';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if old URLs are properly redirected';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Manages_Redirects' );
	}
}
