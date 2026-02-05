<?php
/**
 * GDPR Data Deletion Diagnostic
 *
 * Checks whether GDPR data erasure tools are available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1540
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_GDPR_Data_Deletion Class
 *
 * Validates availability of personal data erasure functionality.
 *
 * @since 1.6035.1540
 */
class Diagnostic_GDPR_Data_Deletion extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-data-deletion';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Data Deletion';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether GDPR data erasure tools are available';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1540
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$erasers = apply_filters( 'wp_privacy_personal_data_erasers', array() );

		if ( empty( $erasers ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No personal data erasers are registered. GDPR deletion capability appears disabled.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-data-deletion',
			);
		}

		return null;
	}
}