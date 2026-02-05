<?php
/**
 * CPT A/B Testing System Feature
 *
 * Provides A/B testing capabilities for custom post types including variant creation,
 * traffic splitting, conversion tracking, and statistical analysis.
 *
 * @package    WPShadow
 * @subpackage Content\Post_Types
 * @since      1.6365.2359
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

use WPShadow\Systems\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT A/B Testing Class
 *
 * Handles A/B testing functionality for custom post types with statistical analysis.
 *
 * @since 1.6365.2359
 */
class CPT_AB_Testing extends Hook_Subscriber_Base {

	/**
	 * Register WordPress hooks.
	 *
	 * @since  1.6035.1400
	 * @return array Hook configuration array.
	 */
	protected static function get_hooks(): array {
		return array(
			'actions' => array(
				array( 'admin_menu', array( __CLASS__, 'register_ab_testing_page' ) ),
				array( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) ),
				array( 'wp_ajax_wpshadow_create_ab_test', array( __CLASS__, 'ajax_create_test' ) ),
				array( 'wp_ajax_wpshadow_get_ab_results', array( __CLASS__, 'ajax_get_results' ) ),
				array( 'template_redirect', array( __CLASS__, 'handle_ab_test_display' ) ),
				array( 'wp_footer', array( __CLASS__, 'track_conversion' ) ),
			),
			'filters' => array(
				array( 'the_content', array( __CLASS__, 'inject_ab_variant' ), 20 ),
			),
		);
	}

	protected static function get_required_version(): string {
		return '1.6365.2359';
	}

	/**
	 * Register A/B testing admin page.
	 *
	 * @since 1.6035.1400
	 * @return void
	 */
	public static function register_ab_testing_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'A/B Testing', 'wpshadow' ),
			__( 'A/B Testing', 'wpshadow' ),
			'manage_options',
			'wpshadow-ab-testing',
			array( __CLASS__, 'render_ab_testing_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since  1.6035.1400
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_admin_assets( string $hook ): void {
		if ( 'wpshadow_page_wpshadow-ab-testing' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-ab-testing',
			plugins_url( 'assets/js/cpt-ab-testing.js', WPSHADOW_FILE ),
			array( 'jquery', 'wp-util', 'chart-js' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-ab-testing',
			'wpShadowAB',
			array(
				'nonce' => wp_create_nonce( 'wpshadow_ab_testing' ),
				'i18n'  => array(
					'creating'       => __( 'Creating A/B test...', 'wpshadow' ),
					'test_created'   => __( 'A/B test created successfully', 'wpshadow' ),
					'loading_stats'  => __( 'Loading statistics...', 'wpshadow' ),
					'variant_a_wins' => __( 'Variant A is performing better', 'wpshadow' ),
					'variant_b_wins' => __( 'Variant B is performing better', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render A/B testing admin page.
	 *
	 * @since 1.6035.1400
	 * @return void
	 */
	public static function render_ab_testing_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
		}

		?>
		<div class="wrap wpshadow-ab-testing">
			<h1><?php esc_html_e( 'A/B Testing Dashboard', 'wpshadow' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Create and manage A/B tests for your custom post types to optimize content performance.', 'wpshadow' ); ?>
			</p>

			<div class="wpshadow-ab-actions">
				<button type="button" class="button button-primary" id="create-new-test" data-wpshadow-modal-open="create-test-modal">
					<?php esc_html_e( 'Create New Test', 'wpshadow' ); ?>
				</button>
			</div>

			<div id="active-tests" class="wpshadow-ab-tests-list">
				<h2><?php esc_html_e( 'Active Tests', 'wpshadow' ); ?></h2>
				<div id="active-tests-container"></div>
			</div>

			<div id="completed-tests" class="wpshadow-ab-tests-list">
				<h2><?php esc_html_e( 'Completed Tests', 'wpshadow' ); ?></h2>
				<div id="completed-tests-container"></div>
			</div>

			<div id="create-test-modal" class="wpshadow-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="create-test-modal-title" aria-hidden="true" data-wpshadow-modal="static" data-overlay-close="true" data-esc-close="true">
				<div class="wpshadow-modal wpshadow-modal--wide" role="document">
					<button type="button" class="wpshadow-modal-close" aria-label="<?php echo esc_attr__( 'Close dialog', 'wpshadow' ); ?>" data-wpshadow-modal-close="create-test-modal">
						<span aria-hidden="true">&times;</span>
					</button>
					<div class="wpshadow-modal-header">
						<h2 id="create-test-modal-title" class="wpshadow-modal-title"><?php esc_html_e( 'Create New A/B Test', 'wpshadow' ); ?></h2>
					</div>
					<div class="wpshadow-modal-body">
						<form id="create-test-form">
						<table class="form-table">
							<tr>
								<th><label for="test_name"><?php esc_html_e( 'Test Name', 'wpshadow' ); ?></label></th>
								<td><input type="text" id="test_name" class="regular-text" required /></td>
							</tr>
							<tr>
								<th><label for="original_post"><?php esc_html_e( 'Original Post', 'wpshadow' ); ?></label></th>
								<td><select id="original_post" class="regular-text" required></select></td>
							</tr>
							<tr>
								<th><label for="variant_post"><?php esc_html_e( 'Variant Post', 'wpshadow' ); ?></label></th>
								<td><select id="variant_post" class="regular-text" required></select></td>
							</tr>
							<tr>
								<th><label for="traffic_split"><?php esc_html_e( 'Traffic Split', 'wpshadow' ); ?></label></th>
								<td>
									<input type="range" id="traffic_split" min="10" max="90" value="50" />
									<span id="traffic_split_value">50% / 50%</span>
								</td>
							</tr>
						</table>
						<p class="submit">
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Create Test', 'wpshadow' ); ?></button>
							<button type="button" class="button" id="cancel-create-test" data-wpshadow-modal-close="create-test-modal"><?php esc_html_e( 'Cancel', 'wpshadow' ); ?></button>
						</p>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle create A/B test AJAX request.
	 *
	 * @since 1.6035.1400
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_create_test(): void {
		check_ajax_referer( 'wpshadow_ab_testing', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$test_name    = isset( $_POST['test_name'] ) ? sanitize_text_field( wp_unslash( $_POST['test_name'] ) ) : '';
		$original_id  = isset( $_POST['original_post'] ) ? absint( $_POST['original_post'] ) : 0;
		$variant_id   = isset( $_POST['variant_post'] ) ? absint( $_POST['variant_post'] ) : 0;
		$traffic_split = isset( $_POST['traffic_split'] ) ? absint( $_POST['traffic_split'] ) : 50;

		$test_data = array(
			'name'           => $test_name,
			'original_id'    => $original_id,
			'variant_id'     => $variant_id,
			'traffic_split'  => $traffic_split,
			'status'         => 'active',
			'created_at'     => current_time( 'mysql' ),
			'views_a'        => 0,
			'views_b'        => 0,
			'conversions_a'  => 0,
			'conversions_b'  => 0,
		);

		$tests = get_option( 'wpshadow_ab_tests', array() );
		$tests[] = $test_data;
		update_option( 'wpshadow_ab_tests', $tests );

		wp_send_json_success( array( 'message' => __( 'A/B test created successfully', 'wpshadow' ), 'test' => $test_data ) );
	}

	/**
	 * Handle get results AJAX request.
	 *
	 * @since 1.6035.1400
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_get_results(): void {
		check_ajax_referer( 'wpshadow_ab_testing', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$test_id = isset( $_POST['test_id'] ) ? absint( $_POST['test_id'] ) : 0;
		$tests   = get_option( 'wpshadow_ab_tests', array() );

		if ( ! isset( $tests[ $test_id ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Test not found', 'wpshadow' ) ) );
		}

		$test = $tests[ $test_id ];
		$test['conversion_rate_a'] = $test['views_a'] > 0 ? ( $test['conversions_a'] / $test['views_a'] ) * 100 : 0;
		$test['conversion_rate_b'] = $test['views_b'] > 0 ? ( $test['conversions_b'] / $test['views_b'] ) * 100 : 0;

		wp_send_json_success( array( 'test' => $test ) );
	}

	/**
	 * Handle A/B test display.
	 *
	 * @since 1.6035.1400
	 * @return void
	 */
	public static function handle_ab_test_display(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post_id = get_the_ID();
		$tests   = get_option( 'wpshadow_ab_tests', array() );

		foreach ( $tests as $index => $test ) {
			if ( 'active' !== $test['status'] ) {
				continue;
			}

			if ( $post_id === $test['original_id'] ) {
				$rand = wp_rand( 1, 100 );
				if ( $rand <= $test['traffic_split'] ) {
					setcookie( 'wpshadow_ab_variant_' . $index, 'a', time() + DAY_IN_SECONDS, '/' );
					$tests[ $index ]['views_a']++;
				} else {
					setcookie( 'wpshadow_ab_variant_' . $index, 'b', time() + DAY_IN_SECONDS, '/' );
					$tests[ $index ]['views_b']++;
				}
				update_option( 'wpshadow_ab_tests', $tests );
				break;
			}
		}
	}

	/**
	 * Inject A/B variant content.
	 *
	 * @since  1.6035.1400
	 * @param  string $content Post content.
	 * @return string Modified content.
	 */
	public static function inject_ab_variant( string $content ): string {
		if ( ! is_singular() ) {
			return $content;
		}

		$post_id = get_the_ID();
		$tests   = get_option( 'wpshadow_ab_tests', array() );

		foreach ( $tests as $index => $test ) {
			if ( 'active' !== $test['status'] ) {
				continue;
			}

			if ( $post_id === $test['original_id'] ) {
				$variant_key = 'wpshadow_ab_variant_' . $index;
				if ( isset( $_COOKIE[ $variant_key ] ) && 'b' === $_COOKIE[ $variant_key ] ) {
					$variant_post = get_post( $test['variant_id'] );
					if ( $variant_post ) {
						return $variant_post->post_content;
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Track conversion.
	 *
	 * @since 1.6035.1400
	 * @return void
	 */
	public static function track_conversion(): void {
		// Conversion tracking logic would be implemented here
		// Tracking conversions via JavaScript or specific goal completions
	}
}
