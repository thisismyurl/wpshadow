<?php
/**
 * Data Retention Schedule Not Documented Diagnostic
 *
 * Checks if data retention is documented.
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
 * Data Retention Schedule Not Documented Diagnostic Class
 *
 * Detects undocumented data retention.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Data_Retention_Schedule_Not_Documented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-retention-schedule-not-documented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Retention Schedule Not Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if data retention is documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if data retention policy is documented
		if ( ! get_option( 'data_retention_policy_documented' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Data retention schedule is not documented. Document your data retention and deletion policies to comply with GDPR and privacy laws.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/data-retention-schedule-not-documented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
