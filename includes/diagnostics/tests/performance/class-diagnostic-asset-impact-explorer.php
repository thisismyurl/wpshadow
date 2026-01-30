<?php
/**
 * Asset Impact Explorer Diagnostic
 *
 * Analyzes page assets (scripts/styles) and their performance impact.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26030.2000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Impact Explorer Diagnostic
 *
 * Detects excessive or poorly-optimized assets on the front end.
 *
 * @since 1.26030.2000
 */
class Diagnostic_Asset_Impact_Explorer extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'asset-impact-explorer';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Asset Impact Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes script/style loading and performance impact';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'utilities';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26030.2000
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$script_count = count( $wp_scripts->queue );
		$style_count  = count( $wp_styles->queue );

		// Flag if too many assets are loaded (industry benchmark: >20 scripts or >15 styles)
		$excessive_scripts = $script_count > 20;
		$excessive_styles  = $style_count > 15;

		if ( $excessive_scripts || $excessive_styles ) {
			$details = array();
			if ( $excessive_scripts ) {
				$details[] = sprintf(
					/* translators: %d: number of scripts */
					__( '%d scripts loaded (over 20 is excessive)', 'wpshadow' ),
					$script_count
				);
			}
			if ( $excessive_styles ) {
				$details[] = sprintf(
					/* translators: %d: number of styles */
					__( '%d stylesheets loaded (over 15 is excessive)', 'wpshadow' ),
					$style_count
				);
			}

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Too many assets loaded on the front end. Use the Asset Impact Explorer to disable unnecessary assets and improve page load speed.', 'wpshadow' ),
				'details'     => implode( '. ', $details ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/asset-impact-explorer',
			);
		}

		return null;
	}
}
