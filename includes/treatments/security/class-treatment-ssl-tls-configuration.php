<?php
/**
 * SSL/TLS Configuration Treatment
 *
 * Analyzes SSL certificate and HTTPS configuration.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL/TLS Configuration Treatment
 *
 * Evaluates SSL certificate validity and security configuration.
 *
 * @since 1.6093.1200
 */
class Treatment_SSL_TLS_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-tls-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL/TLS Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes SSL certificate and HTTPS configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_TLS_Configuration' );
	}
}
