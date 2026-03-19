<?php
/**
 * SSL Certificate Configuration Treatment
 *
 * Checks if SSL certificate is properly configured.
 *
 * @package WPShadow\Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

/**
 * Treatment: SSL Certificate Configuration
 *
 * Detects SSL configuration issues and validity.
 */
class Treatment_SSL_Certificate_Configuration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-configuration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Configuration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates SSL certificate configuration';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'ssl';

	/**
	 * Run the treatment check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Certificate_Configuration' );
	}
}
