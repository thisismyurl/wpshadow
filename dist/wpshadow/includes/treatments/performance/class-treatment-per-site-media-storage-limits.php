<?php
/**
 * Per-Site Media Storage Limits Treatment
 *
 * Tests media quota enforcement for individual sites.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-Site Media Storage Limits Treatment Class
 *
 * Verifies media quota enforcement for individual network sites,
 * including storage limit warnings and enforcement.
 *
 * @since 0.6093.1200
 */
class Treatment_Per_Site_Media_Storage_Limits extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'per-site-media-storage-limits';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Per-Site Media Storage Limits';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media quota enforcement for individual sites';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Per_Site_Media_Storage_Limits' );
	}
}
