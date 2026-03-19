<?php
/**
 * WordPress Updates Available Treatment
 *
 * Checks for pending WordPress core, plugin, or theme updates.
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
 * Treatment_WordPress_Updates_Available Class
 *
 * Detects available updates for WordPress core, plugins, and themes.
 *
 * @since 1.6093.1200
 */
class Treatment_WordPress_Updates_Available extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-updates-available';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Updates Available';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for pending core, plugin, or theme updates';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WordPress_Updates_Available' );
	}
}