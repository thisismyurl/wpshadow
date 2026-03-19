<?php
/**
 * Structured Data For Local Business Not Configured Diagnostic
 *
 * Checks if local business schema is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Structured Data For Local Business Not Configured Diagnostic Class
 *
 * Detects missing local business schema.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Structured_Data_For_Local_Business_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'structured-data-for-local-business-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Structured Data For Local Business Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if local business schema is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for local business info
		if ( ! get_option( 'business_address' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Local business structured data is not configured. Add LocalBusiness schema with address, phone, and hours for local search visibility.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/structured-data-for-local-business-not-configured',
			);
		}

		return null;
	}
}
