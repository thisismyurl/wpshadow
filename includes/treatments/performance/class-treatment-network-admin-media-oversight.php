<?php
/**
 * Network Admin Media Oversight Treatment
 *
 * Tests network admin ability to view/manage all site media.
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
 * Network Admin Media Oversight Treatment Class
 *
 * Verifies network super admin ability to view and manage media
 * across all sites in the network with proper permissions.
 *
 * @since 1.6033.0000
 */
class Treatment_Network_Admin_Media_Oversight extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'network-admin-media-oversight';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Network Admin Media Oversight';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests network admin ability to view/manage all site media';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Network_Admin_Media_Oversight' );
	}
}
