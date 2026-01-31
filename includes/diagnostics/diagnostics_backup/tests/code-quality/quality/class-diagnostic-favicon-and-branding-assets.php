<?php
/**
 * Favicon and Branding Assets Diagnostic
 *
 * Verifies favicon, logo, and brand assets present
 * for professional appearance.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Favicon_And_Branding_Assets Class
 *
 * Verifies branding assets present.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Favicon_And_Branding_Assets extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'favicon-and-branding-assets';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Favicon and Branding Assets';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies favicon and brand assets present';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if branding assets missing, null otherwise.
	 */
	public static function check() {
		$branding_status = self::check_branding_assets();

		if ( ! $branding_status['has_issue'] ) {
			return null; // Branding complete
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: missing asset */
				__( 'Missing %s. Browser tab shows blank icon = unprofessional = trust loss. Takes 5 minutes to add. Every detail matters.', 'wpshadow' ),
				$branding_status['missing_asset']
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/favicon-branding',
			'family'       => self::$family,
			'meta'         => array(
				'branding_complete' => false,
			),
			'details'      => array(
				'branding_assets'                 => array(
					'Favicon' => array(
						'Purpose: Small icon in browser tab',
						'Size: 32×32 or 64×64 pixels',
						'File: favicon.ico or favicon.png',
						'Impact: First impression, professionalism',
					),
					'Logo' => array(
						'Purpose: Header brand identifier',
						'Location: Top left of every page',
						'Size: Typically 200×50 to 400×100',
						'Impact: Brand recognition',
					),
					'Apple Touch Icon' => array(
						'Purpose: iOS home screen icon',
						'Size: 180×180 pixels',
						'Impact: Mobile app-like experience',
					),
					'Open Graph Image' => array(
						'Purpose: Social media preview',
						'Size: 1200×630 pixels',
						'Impact: Sharing on Facebook, LinkedIn',
					),
				),
				'adding_favicon'                  => array(
					'Create Favicon' => array(
						'Online: favicon-generator.org',
						'Upload: Logo image',
						'Generate: Favicon files',
					),
					'Upload Files' => array(
						'FTP: Upload to /wp-content/uploads/',
						'Or: wp-admin → Media → Upload',
					),
					'WordPress Setting' => array(
						'Go to: Customize (wp-admin)',
						'Click: Site Identity',
						'Select: Favicon file',
						'Save: Changes',
					),
				),
				'adding_logo'                     => array(
					'Create Logo' => array(
						'Canva: canva.com (free)',
						'Or: Hire designer (Upwork, Fiverr)',
					),
					'Upload to WordPress' => array(
						'Customize: Site Identity',
						'Logo: Upload your image',
						'Size: WordPress suggests 250-400 width',
					),
					'Theme Setting' => array(
						'Some themes: Custom logo setting',
						'Check: Theme documentation',
					),
				),
				'social_media_assets'             => array(
					'Open Graph Image' => array(
						'Facebook: Link preview image',
						'Setting: Yoast or Rank Math',
						'Size: 1200×630 pixels',
					),
					'Twitter Card' => array(
						'Twitter: Tweet preview image',
						'Setting: Yoast or Rank Math',
						'Size: 1200×675 pixels',
					),
					'LinkedIn Banner' => array(
						'Company page: Background image',
						'Size: 1200×627 pixels',
					),
				),
				'professional_polish'             => array(
					__( 'Favicon: 5 minute improvement, big impact' ),
					__( 'Logo: 30 minute design or hire' ),
					__( 'Consistency: Same brand across all pages' ),
					__( 'Quality: Professional images only' ),
					__( 'Testing: Preview on social media' ),
				),
			),
		);
	}

	/**
	 * Check branding assets.
	 *
	 * @since  1.2601.2148
	 * @return array Branding status.
	 */
	private static function check_branding_assets() {
		$has_issue = false;
		$missing_asset = '';

		// Check favicon
		$favicon = get_option( 'site_icon' );
		if ( empty( $favicon ) ) {
			$has_issue = true;
			$missing_asset = 'favicon';
		}

		// Check logo
		$logo = get_option( 'custom_logo' );
		if ( empty( $logo ) && empty( $missing_asset ) ) {
			$has_issue = true;
			$missing_asset = 'logo';
		}

		return array(
			'has_issue'     => $has_issue,
			'missing_asset' => $missing_asset ?: 'branding assets',
		);
	}
}
