<?php
/**
 * Meta Key Naming Conflicts Treatment
 *
 * Detects meta key naming conflicts between plugins that could cause data overwrites
 * or unexpected behavior. Tests for duplicate or conflicting key patterns.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta Key Naming Conflicts Treatment Class
 *
 * Checks for meta key naming conflicts between plugins.
 *
 * @since 1.6030.2148
 */
class Treatment_Meta_Key_Naming_Conflicts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-key-naming-conflicts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Key Naming Conflicts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects meta key naming conflicts between plugins that cause data issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Meta_Key_Naming_Conflicts' );
	}
}
