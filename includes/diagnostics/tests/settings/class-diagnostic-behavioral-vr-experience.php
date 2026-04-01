<?php
/**
 * Diagnostic: VR Experience Available
 *
 * Tests whether the site offers virtual reality content or tours for immersive
 * engagement in real estate, tourism, education, or events.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4556
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * VR Experience Available Diagnostic
 *
 * Checks for virtual reality content. VR tours/experiences increase engagement
 * for real estate (property tours), tourism (destination previews), education.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Behavioral_VR_Experience extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'offers-vr-experience';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'VR Experience Available';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site offers VR content or tours';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for VR implementation.
	 *
	 * Looks for 360° tours, VR content, and immersive experiences.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for VR/360 tour plugins.
		$vr_plugins = array(
			'wp-vr/wp-vr.php'                                => 'WP VR',
			'ipanorama-360-virtual-tour-builder/ipanorama-virtual-tour-builder.php' => 'iPanorama',
		);

		foreach ( $vr_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has VR capability.
			}
		}

		// Check content for 360° video/tour embeds.
		$posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 30,
				'post_status'    => 'publish',
			)
		);

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			// Check for VR/360 keywords.
			if ( preg_match( '/(360|vr|virtual[\s-]?reality|panorama|matterport)/i', $content ) ) {
				return null; // Has VR content.
			}
		}

		// Only recommend for VR-suitable industries.
		$vr_suitable = false;

		// Check for real estate.
		$property_plugins = array(
			'easy-property-listings/easy-property-listings.php',
			'realtyna-mls-sync/realtyna-mls-sync.php',
		);

		foreach ( $property_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$vr_suitable = true;
				break;
			}
		}

		// Check site content for VR-relevant keywords.
		$site_name = get_bloginfo( 'name' );
		$site_desc = get_bloginfo( 'description' );
		$vr_keywords = array( 'tour', 'property', 'real estate', 'hotel', 'travel', 'museum', 'gallery' );

		foreach ( $vr_keywords as $keyword ) {
			if ( stripos( $site_name, $keyword ) !== false || stripos( $site_desc, $keyword ) !== false ) {
				$vr_suitable = true;
				break;
			}
		}

		if ( ! $vr_suitable ) {
			return null; // Not VR-relevant industry.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No VR content detected for VR-suitable industry. Virtual reality tours/experiences dramatically increase engagement for real estate (property tours), tourism (destination previews), hospitality (hotel rooms), education (virtual labs), events. VR provides immersive previews that build confidence. Consider 360° tours via WP VR plugin or Matterport integration.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/vr-experiences?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
