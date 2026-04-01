<?php
/**
 * Medium Size Configuration Diagnostic
 *
 * Validates medium image size settings and tests dimension accuracy.
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
 * Medium Size Configuration Diagnostic Class
 *
 * Detects when medium image size dimensions are not properly configured,
 * which can lead to suboptimal media handling and inconsistent image display.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Medium_Size_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'medium-size-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Medium Size Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates medium image size settings and tests dimension accuracy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get medium image size settings from WordPress options
		$medium_width  = (int) get_option( 'medium_size_w', 0 );
		$medium_height = (int) get_option( 'medium_size_h', 0 );

		// Define minimum recommended dimensions (300px is WordPress default)
		$min_recommended = 300;

		// Check if dimensions are not set (both are 0)
		if ( 0 === $medium_width && 0 === $medium_height ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Medium image size dimensions are not configured. Both width and height are set to 0. Configure dimensions in Settings > Media to ensure proper image scaling.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/medium-size-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Check if only width is set
		if ( $medium_width > 0 && 0 === $medium_height && $medium_width < $min_recommended ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: current medium width in pixels */
					__( 'Medium image width is set to %dpx, but height is 0. Width should be at least 300px for optimal display. Configure in Settings > Media.', 'wpshadow' ),
					$medium_width
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/medium-size-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Check if only height is set
		if ( $medium_height > 0 && 0 === $medium_width && $medium_height < $min_recommended ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: current medium height in pixels */
					__( 'Medium image height is set to %dpx, but width is 0. Height should be at least 300px for optimal display. Configure in Settings > Media.', 'wpshadow' ),
					$medium_height
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/medium-size-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Check if both dimensions are too small
		if ( $medium_width > 0 && $medium_height > 0 && $medium_width < $min_recommended && $medium_height < $min_recommended ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: current width, 2: current height */
					__( 'Medium image size is configured as %1$dpx x %2$dpx, which is too small. Recommended dimensions are at least 300px x 300px for optimal display across devices.', 'wpshadow' ),
					$medium_width,
					$medium_height
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/medium-size-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Check if width is too small (height is acceptable or 0)
		if ( $medium_width > 0 && $medium_width < $min_recommended ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: current width, 2: current height */
					__( 'Medium image width is only %1$dpx (height: %2$dpx). Width should be at least 300px for optimal display. Configure in Settings > Media.', 'wpshadow' ),
					$medium_width,
					$medium_height
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/medium-size-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Check if height is too small (width is acceptable or 0)
		if ( $medium_height > 0 && $medium_height < $min_recommended ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: current width, 2: current height */
					__( 'Medium image height is only %2$dpx (width: %1$dpx). Height should be at least 300px for optimal display. Configure in Settings > Media.', 'wpshadow' ),
					$medium_width,
					$medium_height
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/medium-size-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// All checks passed - dimensions are properly configured
		return null;
	}
}
