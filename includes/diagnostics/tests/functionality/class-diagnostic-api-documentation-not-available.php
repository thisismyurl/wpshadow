<?php
/**
 * API Documentation Not Available Diagnostic
 *
 * Checks if REST API documentation is available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Documentation Not Available Diagnostic Class
 *
 * Detects missing API documentation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_API_Documentation_Not_Available extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-documentation-not-available';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Documentation Not Available';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API documentation is available';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if REST API is enabled
		if ( ! rest_api_enabled() ) {
			return null;
		}

		// Check for API documentation plugins
		$doc_plugins = array(
			'swagger-documentation/swagger.php',
			'rest-api-documentation/rest-api-documentation.php',
		);

		$doc_active = false;
		foreach ( $doc_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$doc_active = true;
				break;
			}
		}

		if ( ! $doc_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API is enabled but no documentation interface is available. Add API documentation for developers.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/api-documentation-not-available',
			);
		}

		return null;
	}
}
