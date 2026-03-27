<?php
/**
 * Import Blocking Admin Access Treatment
 *
 * Tests whether running imports block admin access to other features.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Blocking Admin Access Treatment Class
 *
 * Tests whether running imports block or restrict admin access to other features.
 *
 * @since 1.6093.1200
 */
class Treatment_Import_Blocking_Admin_Access extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-blocking-admin-access';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Import Blocking Admin Access';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether running imports block admin access to other features';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Import_Blocking_Admin_Access' );
	}
}
