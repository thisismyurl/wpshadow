<?php
/**
 * Treatment: Add Skip Navigation Links
 *
 * Adds accessibility skip links to allow keyboard users to bypass navigation.
 * Implements WCAG 2.1 Level A Success Criterion 2.4.1 (Bypass Blocks).
 *
 * @package    WPShadow
 * @subpackage Treatments\Design
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_No_Skip_Links Class
 *
 * Adds skip navigation links for keyboard accessibility.
 * Creates skip link that appears on focus and jumps to main content.
 *
 * @since 1.6093.1200
 */
class Treatment_No_Skip_Links extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 1.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'no-skip-links';
	}

	/**
	 * Apply the treatment.
	 *
	 * Adds skip navigation link via mu-plugin.
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
		// Check if skip link already exists.
		if ( self::check_existing_skip_link() ) {
			return array(
				'success' => true,
				'message' => __( 'Skip navigation link already exists', 'wpshadow' ),
				'details' => array(
					'action' => 'verified',
					'note'   => __( 'No changes needed', 'wpshadow' ),
				),
			);
		}

		// Create mu-plugin to add skip link.
		$mu_plugin_code = self::get_skip_link_mu_plugin_code();
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
				'message' => __( 'Failed to create skip link mu-plugin', 'wpshadow' ),
				'details' => array(
					'error' => __( 'Could not write to mu-plugins directory', 'wpshadow' ),
					'path'  => $mu_plugin_path,
				),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Added skip navigation link for keyboard accessibility', 'wpshadow' ),
			'details' => array(
				'action'   => 'added_mu_plugin',
				'file'     => 'wpshadow-skip-links.php',
				'features' => array(
					__( 'Skip link appears when Tab key is pressed', 'wpshadow' ),
					__( 'Jumps to main content area', 'wpshadow' ),
					__( 'WCAG 2.1 Level A compliant', 'wpshadow' ),
					__( 'Works with screen readers', 'wpshadow' ),
				),
				'testing'  => __( 'Press Tab on your homepage to see the skip link appear', 'wpshadow' ),
			),
		);
	}

	/**
	 * Check if skip link already exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if skip link exists.
	 */
	private static function check_existing_skip_link() {
		ob_start();
		do_action( 'wp_body_open' );
		$body_output = ob_get_clean();

		return (
			stripos( $body_output, 'skip-to-content' ) !== false ||
			stripos( $body_output, 'skip-link' ) !== false ||
			preg_match( '/<a[^>]+href=["\']#(?:main|content|main-content)["\'][^>]*>/i', $body_output )
		);
	}

	/**
	 * Get MU plugin code for skip navigation link.
	 *
	 * @since 1.6093.1200
	 * @return string MU plugin code.
	 */
	private static function get_skip_link_mu_plugin_code() {
		return <<<'PHP'
<?php
/**
 * WPShadow: Skip Navigation Links
 *
 * Adds accessibility skip link for keyboard navigation.
 * Created by WPShadow design treatment.
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add skip link after body tag opens.
 */
add_action( 'wp_body_open', function() {
	echo '<a href="#main" class="wpshadow-skip-link">' . esc_html__( 'Skip to content', 'wpshadow' ) . '</a>' . "\n";
}, 0 );

/**
 * Add CSS for skip link styling.
 */
add_action( 'wp_head', function() {
	?>
	<style>
		.wpshadow-skip-link {
			position: absolute;
			top: -40px;
			left: 0;
			z-index: 100000;
			padding: 10px 15px;
			background: #000;
			color: #fff;
			text-decoration: none;
			font-size: 14px;
			font-weight: 600;
			line-height: 1;
			transition: top 0.2s ease;
		}
		.wpshadow-skip-link:focus {
			top: 0;
			outline: 2px solid #0073aa;
			outline-offset: 2px;
		}
	</style>
	<?php
}, 1 );

/**
 * Ensure main content area has ID for skip link target.
 * Adds data attribute that JavaScript can use to add ID if missing.
 */
add_action( 'wp_footer', function() {
	?>
	<script>
		(function() {
			if (document.getElementById('main')) return;
			if (document.getElementById('content')) return;
			if (document.getElementById('main-content')) return;
			
			// Try to find main content area and add ID
			var main = document.querySelector('main');
			if (main && !main.id) {
				main.id = 'main';
			} else {
				var content = document.querySelector('[role="main"]');
				if (content && !content.id) {
					content.id = 'main';
				}
			}
		})();
	</script>
	<?php
}, 999 );
PHP;
	}
}
