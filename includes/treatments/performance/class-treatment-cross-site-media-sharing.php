<?php
/**
 * Cross-Site Media Sharing Treatment
 *
 * Tests media library sharing between network sites.
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
 * Cross-Site Media Sharing Treatment Class
 *
 * Verifies media library sharing functionality in multisite networks,
 * including proper permissions and access control.
 *
 * @since 0.6093.1200
 */
class Treatment_Cross_Site_Media_Sharing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cross-site-media-sharing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Cross-Site Media Sharing';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media library sharing between network sites';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Cross_Site_Media_Sharing' );
	}
}
