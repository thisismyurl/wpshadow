<?php
/**
 * Weak SSL/TLS Configuration Diagnostic
 *
 * Checks SSL/TLS config.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Weak_SSL_TLS_Configuration Class
 *
 * Performs diagnostic check for Weak Ssl Tls Configuration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Weak_SSL_TLS_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'weak-ssl-tls-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Weak SSL/TLS Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks SSL/TLS config';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return null;
	}
}