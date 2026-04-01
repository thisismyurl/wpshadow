<?php
/**
 * Treatment: Add Skip Links for Keyboard Navigation
 *
 * Issue #4890: No Skip Links for Keyboard Navigation
 * Pillar: 🌍 Accessibility First
 *
 * Adds skip links to bypass repetitive content.
 * Helps screen reader and keyboard users jump to main content.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Skip_Links_Navigation Class
 *
 * Adds skip links for keyboard accessibility.
 *
 * @since 0.6093.1200
 */
class Treatment_Skip_Links_Navigation extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'skip-links-navigation';
	}

	/**
	 * Apply the treatment.
	 *
	 * Adds skip links to site header via mu-plugin.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about changes made.
	 * }
	 */
	public static function apply() {
		// Create mu-plugin for skip links.
		$mu_plugin_code = self::get_skip_links_mu_plugin();
		$mu_plugin_path = WPMU_PLUGIN_DIR . '/wpshadow-skip-links.php';

		// Create mu-plugins directory if it doesn't exist.
		if ( ! is_dir( WPMU_PLUGIN_DIR ) ) {
			wp_mkdir_p( WPMU_PLUGIN_DIR );
		}

		// Write the mu-plugin file.
		$result = file_put_contents( $mu_plugin_path, $mu_plugin_code );

		if ( $result === false ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to create skip links mu-plugin', 'wpshadow' ),
			);
		}

		// Also add CSS to theme (via customize).
		$skip_css = self::get_skip_links_css();
		$custom_css = wp_get_custom_css();

		// Only add if not already present.
		if ( stripos( $custom_css, 'wpshadow-skip-link' ) === false ) {
			$updated_css = $custom_css . "\n\n" . $skip_css;
			wp_update_custom_css_post( $updated_css );
		}

		return array(
			'success' => true,
			'message' => __( 'Added skip links for keyboard navigation', 'wpshadow' ),
			'details' => array(
				'action'         => 'added_skip_links',
				'file'           => 'wpshadow-skip-links.php',
				'links_added'    => array(
					__( 'Skip to main content', 'wpshadow' ),
					__( 'Skip to footer', 'wpshadow' ),
				),
				'wcag_compliance' => 'WCAG 2.1 2.4.1 Bypass Blocks (Level A)',
				'user_benefit'    => __( 'Keyboard users save 30+ Tab presses per page', 'wpshadow' ),
				'visibility'      => __( 'Skip links visible on keyboard focus', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get MU plugin code for skip links.
	 *
	 * @since 0.6093.1200
	 * @return string MU plugin code.
	 */
	private static function get_skip_links_mu_plugin() {
		return <<<'PHP'
<?php
/**
 * WPShadow: Skip Links
 *
 * Adds skip links for keyboard navigation accessibility.
 * Created by WPShadow accessibility treatment.
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output skip links at start of body.
 */
add_action( 'wp_body_open', function() {
	echo '<div class="wpshadow-skip-links">';
	echo '<a href="#main" class="wpshadow-skip-link">' . esc_html__( 'Skip to main content', 'wpshadow' ) . '</a>';
	echo '<a href="#footer" class="wpshadow-skip-link">' . esc_html__( 'Skip to footer', 'wpshadow' ) . '</a>';
	echo '</div>';
}, 0 );  // Priority 0 to be first.

/**
 * Add IDs to common content areas if missing.
 */
add_filter( 'the_content', function( $content ) {
	// Wrap content in main tag if not already wrapped.
	if ( is_singular() && in_the_loop() && is_main_query() ) {
		static $main_added = false;
		if ( ! $main_added ) {
			$content = '<main id="main" role="main">' . $content . '</main>';
			$main_added = true;
		}
	}
	return $content;
}, 1 );
PHP;
	}

	/**
	 * Get CSS for skip links.
	 *
	 * @since 0.6093.1200
	 * @return string CSS code.
	 */
	private static function get_skip_links_css() {
		return <<<'CSS'
/* WPShadow Skip Links - Accessibility Enhancement */
.wpshadow-skip-links {
	position: relative;
	z-index: 999999;
}

.wpshadow-skip-link {
	position: absolute;
	top: -9999px;
	left: 0;
	background: #000;
	color: #fff;
	padding: 10px 15px;
	text-decoration: none;
	font-size: 14px;
	font-weight: 600;
	z-index: 999999;
	border-radius: 0 0 3px 0;
}

.wpshadow-skip-link:focus {
	top: 0;
	left: 0;
	outline: 3px solid #0073aa;
	outline-offset: 2px;
}

/* Screen reader text */
.sr-only {
	position: absolute;
	width: 1px;
	height: 1px;
	padding: 0;
	margin: -1px;
	overflow: hidden;
	clip: rect(0, 0, 0, 0);
	white-space: nowrap;
	border-width: 0;
}
CSS;
	}
}
