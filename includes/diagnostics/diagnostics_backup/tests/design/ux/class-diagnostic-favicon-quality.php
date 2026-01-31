<?php
/**
 * Favicon Missing or Low Resolution Diagnostic
 *
 * Detects missing or low-resolution favicons that harm brand
 * presentation across browsers and devices.
 *
 * @since   1.6028.1515
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Favicon_Quality Class
 *
 * Checks for favicon presence and validates resolution meets
 * modern browser requirements.
 *
 * @since 1.6028.1515
 */
class Diagnostic_Favicon_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'favicon-missing-low-resolution';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Favicon Missing or Low Resolution';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing or low-resolution favicons that harm professional appearance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ux_branding';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates favicon presence and checks resolution meets
	 * modern standards (at least 32x32, preferably 512x512).
	 *
	 * @since  1.6028.1515
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$favicon_info = self::detect_favicon();

		if ( ! $favicon_info['has_favicon'] ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Your site is missing a favicon, displaying a generic browser icon in tabs and bookmarks. This reduces brand recognition and professional appearance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/favicon-setup',
				'family'        => self::$family,
				'meta'          => array(
					'has_site_icon'     => $favicon_info['has_site_icon'],
					'has_theme_favicon' => $favicon_info['has_theme_favicon'],
					'impact_level'      => __( 'Low - Branding and professionalism', 'wpshadow' ),
					'immediate_actions' => array(
						__( 'Create or generate a 512x512px favicon', 'wpshadow' ),
						__( 'Upload via Appearance > Customize > Site Identity', 'wpshadow' ),
						__( 'Test across browsers (Chrome, Firefox, Safari)', 'wpshadow' ),
					),
				),
				'details'       => array(
					'why_important'    => __( 'Favicons appear in browser tabs, bookmarks, mobile home screens, and browser history. A missing or unprofessional favicon makes your site look incomplete and reduces brand recognition. On mobile devices, favicons are especially important as home screen app icons.', 'wpshadow' ),
					'user_impact'      => array(
						__( 'Tab Recognition: Generic browser icon makes finding your tab difficult', 'wpshadow' ),
						__( 'Bookmarks: Less visual distinction in bookmark lists', 'wpshadow' ),
						__( 'Mobile: No app-style icon when saved to home screen', 'wpshadow' ),
						__( 'Professionalism: Site appears incomplete or amateur', 'wpshadow' ),
					),
					'solution_options' => array(
						'WordPress Built-in' => array(
							'description' => __( 'Use WordPress Site Icon feature', 'wpshadow' ),
							'time'        => __( '5 minutes', 'wpshadow' ),
							'cost'        => __( 'Free', 'wpshadow' ),
							'difficulty'  => __( 'Easy', 'wpshadow' ),
							'steps'       => array(
								__( 'Go to Appearance > Customize > Site Identity', 'wpshadow' ),
								__( 'Click "Select Site Icon"', 'wpshadow' ),
								__( 'Upload 512x512px PNG or JPG', 'wpshadow' ),
								__( 'Publish changes', 'wpshadow' ),
							),
						),
						'Favicon Generator' => array(
							'description' => __( 'Use RealFaviconGenerator.net to create all sizes', 'wpshadow' ),
							'time'        => __( '10 minutes', 'wpshadow' ),
							'cost'        => __( 'Free', 'wpshadow' ),
							'difficulty'  => __( 'Easy', 'wpshadow' ),
						),
						'Design Tool' => array(
							'description' => __( 'Create custom favicon in Figma/Photoshop', 'wpshadow' ),
							'time'        => __( '30-60 minutes', 'wpshadow' ),
							'cost'        => __( 'Free (design time)', 'wpshadow' ),
							'difficulty'  => __( 'Medium', 'wpshadow' ),
						),
					),
					'best_practices'   => array(
						__( 'Size: Create 512x512px source (WordPress generates smaller sizes)', 'wpshadow' ),
						__( 'Format: PNG with transparency for best quality', 'wpshadow' ),
						__( 'Design: Simple, recognizable at tiny sizes (16x16px)', 'wpshadow' ),
						__( 'Colors: Use brand colors for consistency', 'wpshadow' ),
						__( 'Testing: Verify on all major browsers and devices', 'wpshadow' ),
						__( 'Apple Touch Icon: WordPress handles this automatically', 'wpshadow' ),
					),
					'testing_steps'    => array(
						'Step 1' => __( 'Visit site in Chrome, check tab icon', 'wpshadow' ),
						'Step 2' => __( 'Test in Firefox and Safari', 'wpshadow' ),
						'Step 3' => __( 'Test mobile - add to home screen', 'wpshadow' ),
						'Step 4' => __( 'Verify icon in bookmarks list', 'wpshadow' ),
					),
				),
			);
		}

		// Has favicon, check resolution
		if ( $favicon_info['size'] < 32 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %dx%d: favicon dimensions */
					__( 'Your favicon is only %1$dx%2$d pixels, which appears blurry on high-resolution displays. Modern standards require at least 512x512px.', 'wpshadow' ),
					$favicon_info['size'],
					$favicon_info['size']
				),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/favicon-setup',
				'family'        => self::$family,
				'meta'          => array(
					'current_size'      => $favicon_info['size'],
					'recommended_size'  => 512,
					'favicon_path'      => $favicon_info['path'],
					'impact_level'      => __( 'Low - Visual quality on modern displays', 'wpshadow' ),
					'immediate_actions' => array(
						__( 'Replace with 512x512px version', 'wpshadow' ),
						__( 'Test on retina displays', 'wpshadow' ),
					),
				),
				'details'       => array(
					'why_important'    => __( 'Low-resolution favicons appear blurry on retina displays and high-DPI screens. Modern devices expect 192x192 or 512x512 favicons. WordPress automatically generates appropriate sizes from a high-resolution source.', 'wpshadow' ),
					'solution_options' => array(
						'Replace Favicon' => array(
							'description' => __( 'Upload higher resolution version', 'wpshadow' ),
							'time'        => __( '5 minutes', 'wpshadow' ),
							'steps'       => array(
								__( 'Create or find 512x512px version', 'wpshadow' ),
								__( 'Go to Appearance > Customize > Site Identity', 'wpshadow' ),
								__( 'Upload new Site Icon', 'wpshadow' ),
							),
						),
					),
				),
			);
		}

		return null; // Favicon is present and adequate resolution
	}

	/**
	 * Detect favicon presence and properties.
	 *
	 * Checks WordPress Site Icon and fallback favicon methods.
	 *
	 * @since  1.6028.1515
	 * @return array Favicon detection results.
	 */
	private static function detect_favicon() {
		$info = array(
			'has_favicon'       => false,
			'has_site_icon'     => false,
			'has_theme_favicon' => false,
			'size'              => 0,
			'path'              => '',
		);

		// Check WordPress Site Icon (modern method)
		$site_icon_id = get_option( 'site_icon' );
		if ( $site_icon_id ) {
			$info['has_site_icon'] = true;
			$info['has_favicon']   = true;
			
			// Get site icon metadata
			$metadata = wp_get_attachment_metadata( $site_icon_id );
			if ( $metadata && isset( $metadata['width'] ) ) {
				$info['size'] = $metadata['width'];
				$info['path'] = wp_get_attachment_url( $site_icon_id );
			}
			
			return $info;
		}

		// Check legacy theme favicon support
		$favicon_locations = array(
			get_stylesheet_directory() . '/favicon.ico',
			get_template_directory() . '/favicon.ico',
			ABSPATH . 'favicon.ico',
		);

		foreach ( $favicon_locations as $location ) {
			if ( file_exists( $location ) ) {
				$info['has_theme_favicon'] = true;
				$info['has_favicon']       = true;
				$info['path']              = $location;
				
				// Try to get size (ICO files are tricky)
				if ( function_exists( 'getimagesize' ) ) {
					$size = @getimagesize( $location );
					if ( $size && isset( $size[0] ) ) {
						$info['size'] = $size[0];
					} else {
						$info['size'] = 16; // Assume legacy ICO is 16x16
					}
				}
				
				break;
			}
		}

		return $info;
	}
}
