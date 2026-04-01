<?php
/**
 * API Rate Limits Diagnostic
 *
 * Checks if REST API rate limiting is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_API_Rate_Limits Class
 *
 * Ensures API rate limiting is in place to reduce abuse.
 *
 * @since 0.6093.1200
 */
class Diagnostic_API_Rate_Limits extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-rate-limits';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Rate Limits';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether REST API rate limiting is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$rate_limit_plugins = array(
			'wp-rest-api-rate-limit/wp-rest-api-rate-limit.php',
			'rest-api-rate-limit/rest-api-rate-limit.php',
			'wordfence/wordfence.php',
		);

		$enabled = false;
		foreach ( $rate_limit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$enabled = true;
				break;
			}
		}

		if ( ! $enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No API rate limiting detected. Configure rate limits to prevent abuse.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/api-rate-limits?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}