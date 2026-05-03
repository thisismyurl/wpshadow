<?php
/**
 * Custom Logo Set Diagnostic
 *
 * A custom logo reinforces brand identity and professionalism. WordPress
 * provides a standardised custom-logo theme feature since 4.5. When a
 * theme supports it but no logo has been uploaded, the site typically
 * falls back to a generic text header, which weakens brand credibility.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Diagnostics;

use ThisIsMyURL\Shadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Custom_Logo_Set Class
 *
 * @since 0.6095
 */
class Diagnostic_Custom_Logo_Set extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'custom-logo-set';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Custom Logo Set';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that the active theme\'s custom logo slot has been filled with a brand image to reinforce professional identity.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Returns null immediately when the current theme does not declare
	 * custom-logo support (no logo slot to fill). Otherwise checks whether
	 * the custom_logo theme mod is set to a valid attachment ID.
	 *
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// If the theme does not support the custom-logo feature, skip.
		if ( ! current_theme_supports( 'custom-logo' ) ) {
			return null;
		}

		$logo_id = (int) get_theme_mod( 'custom_logo', 0 );

		// A valid logo ID greater than zero means a logo has been set.
		if ( $logo_id > 0 ) {
			$attachment = get_post( $logo_id );
			if ( $attachment && 'attachment' === $attachment->post_type ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The active theme supports a custom logo but none has been uploaded. The site is displaying a generic text header instead of a branded logo image, which reduces perceived professionalism.', 'thisismyurl-shadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'details'      => array(
				'fix' => __( 'Go to Appearance &rsaquo; Customize &rsaquo; Site Identity and upload a logo image. Use an SVG or high-resolution PNG with a transparent background for best results across devices.', 'thisismyurl-shadow' ),
			),
		);
	}
}
