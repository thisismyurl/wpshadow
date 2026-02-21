<?php
/**
 * Incomplete Media Library Export Treatment
 *
 * Detects when media attachments are excluded from exports or
 * only references without files.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7033.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Incomplete Media Library Export Treatment Class
 *
 * Tests for media attachment export completeness.
 *
 * @since 1.7033.1200
 */
class Treatment_Incomplete_Media_Library_Export extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'incomplete-media-library-export';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Incomplete Media Library Export';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects media attachments excluded from exports';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the treatment check.
	 *
	 * Verifies that media attachments are properly included
	 * in export files.
	 *
	 * @since  1.7033.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Incomplete_Media_Library_Export' );
	}
}
