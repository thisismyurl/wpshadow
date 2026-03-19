<?php
/**
 * Reading Settings Not Optimized Treatment
 *
 * Tests for reading/blog page settings.
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
 * Reading Settings Not Optimized Treatment Class
 *
 * Tests for reading/blog page optimization.
 *
 * @since 1.6093.1200
 */
class Treatment_Reading_Settings_Not_Optimized extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'reading-settings-not-optimized';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Reading Settings Not Optimized';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for reading/blog page settings';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Reading_Settings_Not_Optimized' );
	}
}
