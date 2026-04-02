<?php
/**
 * Data Export Capability Diagnostic
 *
 * Checks whether GDPR personal data export tools are available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Data_Export_Capability Class
 *
 * Validates availability of data export functionality.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Data_Export_Capability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-export-capability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Export Capability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether GDPR data export tools are available';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );

		if ( empty( $exporters ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No personal data exporters are registered. GDPR export capability appears disabled.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/data-export-capability',
			);
		}

		return null;
	}
}