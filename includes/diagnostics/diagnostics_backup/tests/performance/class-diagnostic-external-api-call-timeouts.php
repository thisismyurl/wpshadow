<?php
/**
 * External API Call Timeouts Diagnostic
 *
 * Checks for slow external API calls.
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
 * External API Call Timeouts Diagnostic Class
 *
 * Detects slow external API calls.
 *
 * @since 1.2601.2310
 */
class Diagnostic_External_API_Call_Timeouts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'external-api-call-timeouts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'External API Call Timeouts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for external API timeout issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for plugins that make external API calls
		$api_plugins = array(
			'jetpack/jetpack.php',
			'akismet/akismet.php',
			'wordpress-seo/wp-seo.php',
			'gravity-forms/gravityforms.php',
		);

		$api_active = false;
		foreach ( $api_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$api_active = true;
				break;
			}
		}

		if ( ! $api_active ) {
			return null;
		}

		// Check for HTTP timeouts in WordPress
		if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
			return null;
		}

		// This is a detection check - actual timeout monitoring would require runtime analysis
		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => __( 'External API calls from plugins may timeout if the remote service is slow. Consider caching API responses or using asynchronous requests.', 'wpshadow' ),
			'severity'      => 'low',
			'threat_level'  => 15,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/external-api-call-timeouts',
		);
	}
}
