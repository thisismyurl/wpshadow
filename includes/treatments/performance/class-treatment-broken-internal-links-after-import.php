<?php
/**
 * Broken Internal Links After Import Treatment
 *
 * Tests whether internal links remain functional after migration.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broken Internal Links After Import Treatment Class
 *
 * Tests whether internal links point to the correct domain after import/migration.
 *
 * @since 1.6033.0000
 */
class Treatment_Broken_Internal_Links_After_Import extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'broken-internal-links-after-import';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Internal Links After Import';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether internal links remain functional after migration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Broken_Internal_Links_After_Import' );
	}
}
