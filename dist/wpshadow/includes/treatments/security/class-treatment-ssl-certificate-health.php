<?php
/**
 * SSL Certificate Health Treatment
 *
 * Verifies HTTPS support and basic certificate health.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_SSL_Certificate_Health Class
 *
 * Checks that HTTPS is supported and enabled for the site.
 *
 * @since 0.6093.1200
 */
class Treatment_SSL_Certificate_Health extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-health';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Health';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies HTTPS support and SSL configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Certificate_Health' );
	}
}