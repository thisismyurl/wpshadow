<?php
/**
 * Site Icon (Favicon) Presence Diagnostic
 *
 * Verifies that a site icon (favicon) is configured for better branding
 * and improved user experience across browsers and devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Icon (Favicon) Presence Diagnostic Class
 *
 * Ensures site icon/favicon is properly configured.
 *
 * @since 1.26032.1800
 */
class Diagnostic_Site_Icon_Favicon_Presence extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-icon-favicon-presence';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Icon (Favicon) Presence';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site icon (favicon) is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Site icon is configured
	 * - Icon file exists and is valid
	 * - Icon is properly displayed
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get site icon.
		$site_icon_id = get_option( 'site_icon', 0 );

		if ( empty( $site_icon_id ) ) {
			$issues[] = __( 'Site icon (favicon) is not configured; this improves branding and UX', 'wpshadow' );
		} else {
			// Check if image exists.
			$image = wp_get_attachment_image_src( $site_icon_id, 'full' );
			if ( ! $image ) {
				$issues[] = __( 'Site icon is configured but the image is missing or inaccessible', 'wpshadow' );
			} else {
				// Check image dimensions.
				$width  = isset( $image[1] ) ? $image[1] : 0;
				$height = isset( $image[2] ) ? $image[2] : 0;

				if ( $width < 512 || $height < 512 ) {
					$issues[] = sprintf(
						/* translators: 1: image width, 2: image height */
						__( 'Site icon dimensions are too small (%1$dx%2$d); WordPress recommends at least 512x512 pixels', 'wpshadow' ),
						$width,
						$height
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/site-icon-favicon-presence',
			);
		}

		return null;
	}
}
