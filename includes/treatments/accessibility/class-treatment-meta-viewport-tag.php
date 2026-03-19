<?php
/**
 * Treatment: Add Proper Viewport Meta Tag
 *
 * Issue #4973: No Meta Viewport for Mobile
 * Pillar: 🌍 Accessibility First / 🎓 Learning Inclusive
 *
 * Adds proper viewport meta tag for mobile responsiveness.
 * Fixes tiny text on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Meta_Viewport_Tag Class
 *
 * Adds proper viewport meta tag to site header.
 *
 * @since 1.6093.1200
 */
class Treatment_Meta_Viewport_Tag extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 1.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'meta-viewport-tag';
	}

	/**
	 * Apply the treatment.
	 *
	 * Adds proper viewport meta tag to WordPress header.
	 *
	 * @since 1.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about changes made.
	 * }
	 */
	public static function apply() {
		// Check if viewport meta tag already exists in theme.
		$has_viewport = self::check_existing_viewport();
		
		if ( $has_viewport ) {
			return array(
				'success' => true,
				'message' => __( 'Viewport meta tag already present in theme', 'wpshadow' ),
				'details' => array(
					'action' => 'verified',
					'note'   => __( 'No changes needed', 'wpshadow' ),
				),
			);
		}

		// Add viewport meta tag via wp_head action.
		add_action( 'wp_head', array( __CLASS__, 'output_viewport_meta' ), 1 );
		
		// Store this as a mu-plugin for persistence.
		$mu_plugin_code = self::get_viewport_mu_plugin_code();
		$mu_plugin_path = WPMU_PLUGIN_DIR . '/wpshadow-viewport-meta.php';
		
		// Create mu-plugins directory if it doesn't exist.
		if ( ! is_dir( WPMU_PLUGIN_DIR ) ) {
			wp_mkdir_p( WPMU_PLUGIN_DIR );
		}
		
		// Write the mu-plugin file.
		$result = file_put_contents( $mu_plugin_path, $mu_plugin_code );
		
		if ( $result === false ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to create viewport meta tag mu-plugin', 'wpshadow' ),
				'details' => array(
					'error' => __( 'Could not write to mu-plugins directory', 'wpshadow' ),
					'path'  => $mu_plugin_path,
				),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Added proper viewport meta tag for mobile responsiveness', 'wpshadow' ),
			'details' => array(
				'action'    => 'added_mu_plugin',
				'file'      => 'wpshadow-viewport-meta.php',
				'tag'       => '<meta name="viewport" content="width=device-width, initial-scale=1">',
				'impact'    => __( 'Mobile users will now see properly sized text', 'wpshadow' ),
				'seo_boost' => __( 'Improves Google Mobile-Friendly score', 'wpshadow' ),
			),
		);
	}

	/**
	 * Check if viewport meta tag already exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if viewport exists.
	 */
	private static function check_existing_viewport() {
		// Check if theme already outputs viewport.
		ob_start();
		do_action( 'wp_head' );
		$head_output = ob_get_clean();
		
		return ( stripos( $head_output, 'viewport' ) !== false );
	}

	/**
	 * Output viewport meta tag.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function output_viewport_meta() {
		echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
	}

	/**
	 * Get MU plugin code for viewport meta tag.
	 *
	 * @since 1.6093.1200
	 * @return string MU plugin code.
	 */
	private static function get_viewport_mu_plugin_code() {
		return <<<'PHP'
<?php
/**
 * WPShadow: Viewport Meta Tag
 *
 * Adds proper viewport meta tag for mobile responsiveness.
 * Created by WPShadow accessibility treatment.
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add viewport meta tag to header.
 */
add_action( 'wp_head', function() {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
}, 1 );
PHP;
	}
}
