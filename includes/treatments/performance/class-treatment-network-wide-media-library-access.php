<?php
/**
 * Network-Wide Media Library Access Treatment
 *
 * Tests global media library functionality across network.
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
 * Network-Wide Media Library Access Treatment Class
 *
 * Verifies global media library functionality for network installations,
 * including network admin capabilities and cross-site access.
 *
 * @since 1.6093.1200
 */
class Treatment_Network_Wide_Media_Library_Access extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'network-wide-media-library-access';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Network-Wide Media Library Access';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests global media library functionality across network';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Network_Wide_Media_Library_Access' );
	}
}
