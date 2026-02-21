<?php
/**
 * Custom Permalink Structure Treatment
 *
 * Detects posts using custom permalinks and analyzes potential conflicts.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1745
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Permalink Structure Treatment Class
 *
 * Checks for posts with custom permalinks set via post meta.
 *
 * @since 1.6032.1745
 */
class Treatment_Custom_Permalink_Structure extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-permalink-structure';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Permalink Structure';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects custom permalink usage';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1745
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Custom_Permalink_Structure' );
	}
}
