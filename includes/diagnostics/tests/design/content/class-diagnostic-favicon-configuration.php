<?php
/**
 * Favicon Configuration Diagnostic
 *
 * Issue #4972: No Favicon (Branding Opportunity)
 * Pillar: 🎓 Learning Inclusive / #1: Helpful Neighbor
 *
 * Checks if site has favicon configured.
 * Favicon helps users recognize site in tabs and bookmarks.
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
 * Diagnostic_Favicon_Configuration Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Favicon_Configuration extends Diagnostic_Base {

	protected static $slug = 'favicon-configuration';
	protected static $title = 'No Favicon (Branding Opportunity)';
	protected static $description = 'Checks if site has favicon configured';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add favicon.ico to site root', 'wpshadow' );
		$issues[] = __( 'Create 16x16, 32x32, 64x64 versions', 'wpshadow' );
		$issues[] = __( 'Add apple-touch-icon for iOS home screen', 'wpshadow' );
		$issues[] = __( 'Add web app manifest for PWA support', 'wpshadow' );
		$issues[] = __( 'Use clear, recognizable image', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Favicon appears in browser tabs and bookmarks. It helps users recognize your site and improves brand recognition.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/favicon',
				'details'      => array(
					'recommendations'         => $issues,
					'benefits'                => 'Branding, tab recognition, bookmarks',
					'location'                => 'https://yoursite.com/favicon.ico',
					'html_tag'                => '<link rel="icon" href="/favicon.ico" sizes="any">',
				),
			);
		}

		return null;
	}
}
