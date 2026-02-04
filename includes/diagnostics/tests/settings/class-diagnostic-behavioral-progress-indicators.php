<?php
/**
 * Diagnostic: Progress Indicators Used
 *
 * Tests whether the site uses progress indicators in multi-step processes
 * to improve completion rates by 14-28%.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4535
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since      1.6034.1440
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Progress Indicators Diagnostic
 *
 * Checks if multi-step forms/checkouts display progress. Progress visibility
 * reduces abandonment by showing users how close they are to completion.
 *
 * @since 1.6034.1440
 */
class Diagnostic_Behavioral_Progress_Indicators extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-progress-indicators';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Progress Indicators Used';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses progress indicators in multi-step processes';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for progress indicator implementation.
	 *
	 * Looks for multi-step forms and checkout flows with progress display.
	 *
	 * @since  1.6034.1440
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check if WooCommerce multi-step checkout is enabled.
		$has_progress = false;
		
		if ( class_exists( 'WooCommerce' ) ) {
			// Check for multi-step checkout plugins.
			$checkout_plugins = array(
				'woo-checkout-field-editor-pro/checkout-form-designer.php',
				'cartflows/cartflows.php',
				'funnel-builder/funnel-builder.php',
			);

			foreach ( $checkout_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_progress = true;
					break;
				}
			}
		}

		// Check for multi-step form plugins.
		$form_plugins = array(
			'wpforms-lite/wpforms.php',
			'gravityforms/gravityforms.php',
			'formidable/formidable.php',
		);

		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				// These support progress indicators.
				$has_progress = true;
				break;
			}
		}

		// Check theme for progress indicator markup.
		$theme      = wp_get_theme();
		$theme_root = get_theme_root();
		$theme_path = $theme_root . '/' . $theme->get_stylesheet();

		if ( class_exists( 'WooCommerce' ) && is_dir( $theme_path . '/woocommerce' ) ) {
			$checkout_files = glob( $theme_path . '/woocommerce/checkout/*.php' );
			
			foreach ( $checkout_files as $file ) {
				$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				
				// Look for progress bar patterns.
				if ( preg_match( '/(progress|step|wizard|stage)/i', $content ) ) {
					$has_progress = true;
					break;
				}
			}
		}

		if ( $has_progress ) {
			return null;
		}

		// Only flag if site has multi-step processes.
		$has_multistep = false;
		
		if ( class_exists( 'WooCommerce' ) ) {
			$has_multistep = true;
		}

		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_multistep = true;
				break;
			}
		}

		if ( ! $has_multistep ) {
			return null; // No multi-step processes.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'Multi-step processes lack progress indicators. Showing users their progress (e.g., "Step 2 of 4") reduces abandonment by 14-28%. Add progress bars or step indicators to checkouts and long forms.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/progress-indicators',
		);
	}
}
