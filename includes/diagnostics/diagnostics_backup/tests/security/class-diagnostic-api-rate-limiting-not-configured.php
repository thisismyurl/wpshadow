<?php
/**
 * API Rate Limiting Not Configured Diagnostic
 *
 * Checks if API rate limiting is implemented.
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
 * API Rate Limiting Not Configured Diagnostic Class
 *
 * Detects missing API rate limiting.
 *
 * @since 1.2601.2310
 */
class Diagnostic_API_Rate_Limiting_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-rate-limiting-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Rate Limiting Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API rate limiting is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

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

		// Check for rate limiting plugins
		$ratelimit_plugins = array(
			'rest-api-toolbox/rest-api-toolbox.php',
			'wordfence/wordfence.php',
		);

		$ratelimit_active = false;
		foreach ( $ratelimit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$ratelimit_active = true;
				break;
			}
		}

		if ( ! $ratelimit_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'API rate limiting is not configured. Attackers can make unlimited requests to your REST API, causing DoS attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/api-rate-limiting-not-configured',
			);
		}

		return null;
	}
}
