<?php
/**
 * Accelerated Mobile Pages Not Implemented Diagnostic
 *
 * Checks AMP implementation.
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
 * Diagnostic_Accelerated_Mobile_Pages_Not_Implemented Class
 *
 * Performs diagnostic check for Accelerated Mobile Pages Not Implemented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Accelerated_Mobile_Pages_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accelerated-mobile-pages-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accelerated Mobile Pages Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks AMP implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$amp_plugins = array(
			'amp/amp.php'                           => 'AMP',
			'better-amp/better-amp.php'             => 'Better AMP',
			'weeblramp/weeblramp.php'               => 'weeblrAMP',
			'ampforwp/accelerated-mobile-pages.php' => 'AMP for WP',
		);

		$plugin_detected = false;
		$plugin_name     = '';

		foreach ( $amp_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				$plugin_name     = $name;
				break;
			}
		}

		// AMP plugin adds theme support via add_theme_support( 'amp' ).
		$has_amp_support = current_theme_supports( 'amp' ) || function_exists( 'amp_is_request' );

		if ( ! $plugin_detected && ! $has_amp_support ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				/* translators: %s: AMP plugin name */
				'description'  => __( 'Your site does not appear to use Accelerated Mobile Pages (AMP). AMP is an optional way to serve lighter pages for mobile visitors. It can help on slow connections, but it is not required for a fast site. If you choose to use AMP, install a trusted AMP plugin and test your pages carefully.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/accelerated-mobile-pages-not-implemented',
				'details'      => array(
					'has_amp_plugin'  => false,
					'has_amp_support' => false,
					'recommendation'  => __( 'If mobile visitors use slow networks, consider AMP to reduce page weight. If your site is already fast and responsive, AMP may not be necessary.', 'wpshadow' ),
					'common_plugins'  => array_values( $amp_plugins ),
				),
			);
		}

		if ( $plugin_detected && ! $has_amp_support ) {
			return array(
				'id'           => self::$slug,
				'title'        => __( 'AMP Plugin Active, Theme Support Missing', 'wpshadow' ),
				/* translators: %s: AMP plugin name */
				'description'  => sprintf( __( 'The %s plugin is active, but your theme does not report AMP support yet. AMP pages may not be generated. Check your theme documentation for AMP compatibility and ensure the AMP plugin setup is complete.', 'wpshadow' ), $plugin_name ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/accelerated-mobile-pages-not-implemented',
				'details'      => array(
					'plugin_name'     => $plugin_name,
					'has_amp_support' => false,
					'recommendation'  => __( 'Finish the AMP plugin setup and confirm your theme supports AMP. Many themes require a small configuration step.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
