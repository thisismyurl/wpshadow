<?php
/**
 * REST API Not Secured Diagnostic
 *
 * Checks if REST API has proper security.
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
 * REST API Not Secured Diagnostic Class
 *
 * Detects insecure REST API configuration.
 *
 * @since 1.2601.2310
 */
class Diagnostic_REST_API_Not_Secured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-not-secured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Not Secured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API is properly secured';

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
			return null; // Disabled is intentional
		}

		// Check for REST API security plugins
		$security_plugins = array(
			'rest-api-toolbox/rest-api-toolbox.php',
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
		);

		$security_active = false;
		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$security_active = true;
				break;
			}
		}

		if ( ! $security_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API is enabled but no security plugin is protecting it. Unprotected REST API endpoints can expose sensitive information or be used for attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-not-secured',
			);
		}

		return null;
	}
}
