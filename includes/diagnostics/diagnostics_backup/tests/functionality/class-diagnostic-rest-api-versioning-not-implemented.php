<?php
/**
 * REST API Versioning Not Implemented Diagnostic
 *
 * Checks if REST API has versioning.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2335
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Versioning Not Implemented Diagnostic Class
 *
 * Detects missing API versioning.
 *
 * @since 1.2601.2335
 */
class Diagnostic_REST_API_Versioning_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-versioning-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Versioning Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API has versioning';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site is using REST API
		if ( ! rest_get_url_prefix() ) {
			return null;
		}

		// Check for custom endpoint versioning
		if ( ! has_filter( 'rest_api_init' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API endpoints are not versioned. Implement versioning to prevent breaking API changes.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-versioning-not-implemented',
			);
		}

		return null;
	}
}
