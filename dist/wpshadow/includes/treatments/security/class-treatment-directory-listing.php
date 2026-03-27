<?php
/**
 * Directory Listing Enabled Treatment
 *
 * Checks for directory indexes on upload directories, verifies .htaccess blocks
 * directory browsing, and tests for sensitive file exposure.
 *
 * @package    WPShadow
 * @subpackage Treatments\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Listing Enabled Treatment Class
 *
 * Detects directory listing vulnerabilities that could expose sensitive
 * files and directory structures to attackers.
 *
 * @since 1.6093.1200
 */
class Treatment_Directory_Listing extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'blocks_directory_listing';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Directory Listing Enabled';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies directory browsing is disabled to prevent file exposure';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Directory_Listing' );
	}
}
