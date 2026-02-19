<?php
/**
 * Zero Configuration Deployment Not Available Diagnostic
 *
 * Checks zero-config deployment.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Zero_Configuration_Deployment_Not_Available Class
 *
 * Performs diagnostic check for Zero Configuration Deployment Not Available.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Zero_Configuration_Deployment_Not_Available extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'zero-configuration-deployment-not-available';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Zero Configuration Deployment Not Available';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks zero-config deployment';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'zero_config_deployment_enabled' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Zero-configuration deployment is not enabled yet. Simplifying deployment setup can reduce manual steps and lower release risk.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/zero-configuration-deployment-not-available',
			);
		}

		return null;
	}
}
