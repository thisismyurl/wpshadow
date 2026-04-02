<?php
/**
 * JPEG Quality Configured Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 56.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JPEG Quality Configured Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Jpeg_Quality_extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'jpeg-quality';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'JPEG Quality';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for JPEG Quality Configured. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check wp_editor_set_quality filter output value.
	 *
	 * TODO Fix Plan:
	 * Fix by setting balanced quality level.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		$quality = WP_Settings::get_jpeg_quality();

		// 60–85 is the sensible working range — good quality with manageable file sizes.
		if ( $quality >= 60 && $quality <= 85 ) {
			return null;
		}

		if ( $quality > 85 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: current JPEG quality */
					__( 'JPEG compression quality is set to %d/100, which is high. At this level, JPEG files are substantially larger than necessary. Reducing quality to 75–82 typically produces images that are visually indistinguishable while cutting file sizes by 20–50%%.', 'wpshadow' ),
					$quality
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/jpeg-quality',
				'details'      => array(
					'current_quality'    => $quality,
					'recommended_range'  => '75–82',
				),
			);
		}

		// Quality < 60 — too low, visible artefacts.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: current JPEG quality */
				__( 'JPEG compression quality is set to %d/100, which is very low. Images compressed at this level will show visible blocking and colour artefacts. Increase quality to at least 70 for an acceptable visual result.', 'wpshadow' ),
				$quality
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/jpeg-quality',
			'details'      => array(
				'current_quality'   => $quality,
				'recommended_range' => '75–82',
			),
		);
	}
}
