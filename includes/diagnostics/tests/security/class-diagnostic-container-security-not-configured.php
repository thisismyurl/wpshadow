<?php
/**
 * Container Security Not Configured Diagnostic
 *
 * Checks container security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Container_Security_Not_Configured Class
 *
 * Performs diagnostic check for Container Security Not Configured.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Container_Security_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'container-security-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Container Security Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks container security';

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
		if ( ! has_filter( 'init', 'verify_container_security' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Container security checks are not configured yet. Enabling image and runtime hardening checks helps reduce risk.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
