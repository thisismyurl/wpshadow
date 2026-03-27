<?php
/**
 * Treatment: Add Call-to-Action System
 *
 * Provides CTA tools and templates to recover conversion opportunities.
 * Creates shortcode and block system for easy CTA insertion.
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
 * Treatment_Missing_CTA Class
 *
 * Adds CTA framework and tools to help users add calls-to-action.
 * Provides shortcodes, templates, and guidance for effective CTAs.
 *
 * @since 1.6093.1200
 */
class Treatment_Missing_CTA extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 1.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'missing-cta';
	}

	/**
	 * Apply the treatment.
	 *
	 * Installs CTA framework with shortcodes and templates.
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
		// Create mu-plugin with CTA system.
		$mu_plugin_code = self::get_cta_mu_plugin_code();
		$mu_plugin_path = WPMU_PLUGIN_DIR . '/wpshadow-cta-system.php';

		// Create mu-plugins directory if it doesn't exist.
		if ( ! is_dir( WPMU_PLUGIN_DIR ) ) {
			wp_mkdir_p( WPMU_PLUGIN_DIR );
		}

		// Write the mu-plugin file.
		$result = file_put_contents( $mu_plugin_path, $mu_plugin_code );

		if ( $result === false ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to create CTA system mu-plugin', 'wpshadow' ),
				'details' => array(
					'error' => __( 'Could not write to mu-plugins directory', 'wpshadow' ),
					'path'  => $mu_plugin_path,
				),
			);
		}

		// Register default CTA options.
		add_option( 'wpshadow_default_cta_text', __( 'Get Started', 'wpshadow' ) );
		add_option( 'wpshadow_default_cta_url', home_url() );
		add_option( 'wpshadow_cta_style', 'primary' );

		return array(
			'success' => true,
			'message' => __( 'Installed CTA system with shortcodes and templates', 'wpshadow' ),
			'details' => array(
				'action'    => 'added_mu_plugin',
				'file'      => 'wpshadow-cta-system.php',
				'features'  => array(
					__( '[wpshadow_cta] shortcode for easy CTA insertion', 'wpshadow' ),
					__( 'Customizable button styles', 'wpshadow' ),
					__( 'Pre-designed CTA templates', 'wpshadow' ),
					__( 'Analytics-ready tracking attributes', 'wpshadow' ),
				),
				'usage'     => array(
					'basic'    => '[wpshadow_cta text="Download Free Guide" url="/download"]',
					'styled'   => '[wpshadow_cta text="Get Started" url="/signup" style="primary" size="large"]',
					'tracked'  => '[wpshadow_cta text="Learn More" url="/learn" track="blog-post-cta"]',
				),
				'next_steps' => array(
					__( '1. Edit posts without CTAs', 'wpshadow' ),
					__( '2. Add [wpshadow_cta] shortcode where appropriate', 'wpshadow' ),
					__( '3. Customize text and URL for your goal', 'wpshadow' ),
					__( '4. Test CTA placement and effectiveness', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Get MU plugin code for CTA system.
	 *
	 * @since 1.6093.1200
	 * @return string MU plugin code.
	 */
	private static function get_cta_mu_plugin_code() {
		return <<<'PHP'
<?php
/**
 * WPShadow: CTA System
 *
 * Provides shortcodes and tools for adding calls-to-action.
 * Created by WPShadow design treatment.
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register CTA shortcode.
 */
add_shortcode( 'wpshadow_cta', function( $atts ) {
	$atts = shortcode_atts( array(
		'text'  => get_option( 'wpshadow_default_cta_text', 'Get Started' ),
		'url'   => get_option( 'wpshadow_default_cta_url', home_url() ),
		'style' => get_option( 'wpshadow_cta_style', 'primary' ),
		'size'  => 'medium',
		'track' => '',
	), $atts );

	$classes = array(
		'wpshadow-cta',
		'wpshadow-cta--' . esc_attr( $atts['style'] ),
		'wpshadow-cta--' . esc_attr( $atts['size'] ),
	);

	$data_attrs = '';
	if ( ! empty( $atts['track'] ) ) {
		$data_attrs = ' data-track="' . esc_attr( $atts['track'] ) . '"';
	}

	return sprintf(
		'<p class="wpshadow-cta-wrapper"><a href="%s" class="%s"%s>%s</a></p>',
		esc_url( $atts['url'] ),
		esc_attr( implode( ' ', $classes ) ),
		$data_attrs,
		esc_html( $atts['text'] )
	);
} );

/**
 * Add CTA CSS styles.
 */
add_action( 'wp_head', function() {
	?>
	<style>
		.wpshadow-cta-wrapper {
			text-align: center;
			margin: 2em 0;
		}
		.wpshadow-cta {
			display: inline-block;
			padding: 12px 24px;
			text-decoration: none;
			font-weight: 600;
			border-radius: 4px;
			transition: all 0.3s ease;
			cursor: pointer;
		}
		.wpshadow-cta--primary {
			background: #0073aa;
			color: #fff;
			border: 2px solid #0073aa;
		}
		.wpshadow-cta--primary:hover {
			background: #005a87;
			border-color: #005a87;
		}
		.wpshadow-cta--secondary {
			background: #fff;
			color: #0073aa;
			border: 2px solid #0073aa;
		}
		.wpshadow-cta--secondary:hover {
			background: #0073aa;
			color: #fff;
		}
		.wpshadow-cta--small {
			padding: 8px 16px;
			font-size: 14px;
		}
		.wpshadow-cta--large {
			padding: 16px 32px;
			font-size: 18px;
		}
	</style>
	<?php
}, 20 );

/**
 * Add admin notice to guide CTA usage.
 */
add_action( 'admin_notices', function() {
	$screen = get_current_screen();
	if ( $screen && 'post' === $screen->base ) {
		?>
		<div class="notice notice-info is-dismissible">
			<p><strong>WPShadow CTA System:</strong> Add calls-to-action with <code>[wpshadow_cta text="Your Text" url="/your-url"]</code></p>
		</div>
		<?php
	}
} );
PHP;
	}
}

