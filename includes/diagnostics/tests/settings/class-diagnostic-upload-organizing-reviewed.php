<?php
/**
 * Upload Organizing Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Upload_Organizing_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Upload_Organizing_Reviewed extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'upload-organizing-reviewed';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Upload Organizing Reviewed';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for Upload Organizing Reviewed';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check uploads_use_yearmonth_folders option for intentional media organization.
	 *
	 * TODO Fix Plan:
	 * - Enable or disable monthly folders based on media governance needs.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// Year/month folder organisation is the recommended default; flag only when disabled.
		if ( WP_Settings::uses_year_month_folders() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Organising uploads into year/month sub-folders is disabled. On sites with many media files this causes all uploads to pile up in a single directory, which can slow down file-system operations and media library queries. Enable this option in Settings > Media unless you have a specific reason to keep it off.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 10,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/upload-organizing',
			'details'      => array(
				'uploads_use_yearmonth_folders' => false,
			),
		);
	}
}
