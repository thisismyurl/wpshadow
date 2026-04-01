<?php
/**
 * Encryption in Transit Diagnostic
 *
 * Ensures HTTPS is enabled for data in transit.
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
 * Diagnostic_Encryption_In_Transit Class
 *
 * Validates HTTPS configuration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Encryption_In_Transit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'encryption-in-transit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Encryption in Transit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether HTTPS is enabled for data in transit';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! wp_is_https_supported() || 0 !== strpos( home_url(), 'https://' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'HTTPS is not fully enabled. Encrypt data in transit to meet compliance requirements.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/encryption-in-transit?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}