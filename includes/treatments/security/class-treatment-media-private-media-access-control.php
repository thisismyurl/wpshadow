<?php
/**
 * Media Private Media Access Control Treatment
 *
 * Tests access control for private/restricted media files.
 * Validates that permission checks are properly enforced.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Private_Media_Access_Control Class
 *
 * Checks if private and restricted media files have proper access controls.
 * Tests for:
 * - Private attachment permission checks
 * - Protected media access restrictions
 * - User role-based access control
 * - Media metadata privacy handling
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Private_Media_Access_Control extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-private-media-access-control';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Private Media Access Control';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests access control for private/restricted media files';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Private_Media_Access_Control' );
	}
}
