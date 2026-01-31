<?php
/**
 * Content Delivery Network Integration Not Tested Diagnostic
 *
 * Checks if CDN integration is tested.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Delivery Network Integration Not Tested Diagnostic Class
 *
 * Detects untested CDN integration.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Content_Delivery_Network_Integration_Not_Tested extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-delivery-network-integration-not-tested';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Delivery Network Integration Not Tested';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CDN integration is tested';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if CDN is configured and tested
		if ( ! get_option( 'cdn_integration_test_date' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CDN integration is not tested. Verify that static assets are being served from your CDN and that purge events work correctly.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-delivery-network-integration-not-tested',
			);
		}

		return null;
	}
}
