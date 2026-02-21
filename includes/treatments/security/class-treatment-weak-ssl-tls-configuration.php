<?php
/**
 * Weak SSL/TLS Configuration Treatment
 *
 * Checks SSL/TLS config.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Weak_SSL_TLS_Configuration Class
 *
 * Performs treatment check for Weak Ssl Tls Configuration.
 *
 * @since 1.6033.2033
 */
class Treatment_Weak_SSL_TLS_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'weak-ssl-tls-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Weak SSL/TLS Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks SSL/TLS config';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Weak_SSL_TLS_Configuration' );
	}
}
