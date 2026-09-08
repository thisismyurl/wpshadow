<?php
/**
 * Dashboard Integrations
 *
 * Adds Shadow by Christopher Ross integrations to the WordPress dashboard, admin bar, and Site Health.
 *
 * @package    Shadow by Christopher Ross
 * @subpackage Admin
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard_Integrations Class
 *
 * Provides Shadow by Christopher Ross entry points inside standard WordPress admin surfaces.
 *
 * @since 0.6095
 */
class Dashboard_Integrations extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since 0.6095
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'actions' => array(
				array( 'wp_dashboard_setup', 'register_dashboard_widget', 10, 0 ),
				array( 'admin_bar_menu', 'add_admin_bar_item', 90, 1 ),
			),
			'filters' => array(
				array( 'site_health_tests', 'filter_site_health_tests', 10, 1 ),
			),
		);
	}

	/**
	 * Register dashboard widget.
	 *
	 * @since 0.6095
	 * @return void
	 */
	public static function register_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'wpshadow_overview_widget',
			__( 'Shadow Overview', 'wpshadow' ),
			array( __CLASS__, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @since 0.6095
	 * @return void
	 */
	public static function render_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$count     = self::get_open_findings_count();
		$last_scan = self::get_last_scan_timestamp();
		$label     = sprintf(
			/* translators: %s: number of open findings */
			_n( '%s open item', '%s open items', $count, 'wpshadow' ),
			number_format_i18n( $count )
		);
		$last_scan_text = $last_scan > 0
			? sprintf(
				/* translators: %s: human-readable time difference */
				__( 'Last scan %s ago.', 'wpshadow' ),
				human_time_diff( $last_scan, time() )
			)
			: __( 'No scans yet. Run your first scan to set a baseline.', 'wpshadow' );

		$dashboard_url = admin_url( 'admin.php?page=wpshadow' );
		?>
		<div class="wpshadow-dashboard-widget">
			<p>
				<?php esc_html_e( 'Shadow by Christopher Ross runs locally inside your WordPress site and checks for issues you can fix here.', 'wpshadow' ); ?>
			</p>
			<p>
				<strong><?php echo esc_html( $label ); ?></strong>
			</p>
			<p><?php echo esc_html( $last_scan_text ); ?></p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( $dashboard_url ); ?>">
					<?php esc_html_e( 'Open Shadow Dashboard', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Add Shadow by Christopher Ross entry to the admin bar.
	 *
	 * @since 0.6095
	 * @param  \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public static function add_admin_bar_item( $wp_admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) || ! is_admin_bar_showing() ) {
			return;
		}

		$count         = self::get_open_findings_count();
		$dashboard_url = admin_url( 'admin.php?page=wpshadow' );
		$title         = $count > 0
			? sprintf(
				/* translators: %s: number of open findings */
				__( 'Shadow by Christopher Ross (%s)', 'wpshadow' ),
				number_format_i18n( $count )
			)
			: __( 'Shadow by Christopher Ross', 'wpshadow' );

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wpshadow-overview',
				'title' => esc_html( $title ),
				'href'  => esc_url( $dashboard_url ),
				'meta'  => array(
					'title' => esc_attr__( 'Open Shadow Dashboard', 'wpshadow' ),
				),
			)
		);

		$wp_admin_bar->add_node(
			array(
				'parent' => 'wpshadow-overview',
				'id'     => 'wpshadow-overview-dashboard',
				'title'  => esc_html__( 'View Dashboard', 'wpshadow' ),
				'href'   => esc_url( $dashboard_url ),
			)
		);
	}

	/**
	 * Register Site Health tests for Shadow by Christopher Ross.
	 *
	 * @since 0.6095
	 * @param  array $tests Site Health tests.
	 * @return array Updated Site Health tests.
	 */
	public static function filter_site_health_tests( array $tests ): array {
		$tests['direct']['wpshadow_overview'] = array(
			'label' => __( 'Shadow by Christopher Ross overview', 'wpshadow' ),
			'test'  => array( __CLASS__, 'get_site_health_test' ),
		);

		return $tests;
	}

	/**
	 * Provide the Site Health test result.
	 *
	 * @since 0.6095
	 * @return array Site Health test result.
	 */
	public static function get_site_health_test(): array {
		$count         = self::get_open_findings_count();
		$last_scan     = self::get_last_scan_timestamp();
		$dashboard_url = admin_url( 'admin.php?page=wpshadow' );
		$badge         = array(
			'label' => 'Shadow by Christopher Ross',
			'color' => 'blue',
		);

		if ( 0 === $last_scan ) {
			return array(
				'label'       => __( 'Shadow by Christopher Ross has not run a scan yet', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => $badge,
				'description' => sprintf(
					'<p>%s</p>',
					esc_html__( 'Shadow by Christopher Ross runs locally inside your WordPress site. Running a first scan gives you a clear starting point.', 'wpshadow' )
				),
				'actions'     => sprintf(
					'<p><a class="button" href="%s">%s</a></p>',
					esc_url( $dashboard_url ),
					esc_html__( 'Open Shadow by Christopher Ross', 'wpshadow' )
				),
				'test'        => 'wpshadow_overview',
			);
		}

		if ( $count > 0 ) {
			return array(
				'label'       => __( 'Shadow by Christopher Ross found items to review', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => $badge,
				'description' => sprintf(
					'<p>%s</p>',
					esc_html__( 'These checks run locally inside your WordPress site. Reviewing them can improve security and performance.', 'wpshadow' )
				),
				'actions'     => sprintf(
					'<p><a class="button" href="%s">%s</a></p>',
					esc_url( $dashboard_url ),
					esc_html__( 'Review in Shadow by Christopher Ross', 'wpshadow' )
				),
				'test'        => 'wpshadow_overview',
			);
		}

		return array(
			'label'       => __( 'Shadow by Christopher Ross checks look good', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => $badge,
			'description' => sprintf(
				'<p>%s</p>',
				esc_html__( 'Your last scan did not find anything that needs attention right now.', 'wpshadow' )
			),
			'test'        => 'wpshadow_overview',
		);
	}

	/**
	 * Get the count of active findings.
	 *
	 * @since 0.6095
	 * @return int Active findings count.
	 */
	private static function get_open_findings_count(): int {
		$findings = function_exists( 'wpshadow_get_site_findings' )
			? wpshadow_get_site_findings()
			: get_option( 'wpshadow_site_findings', array() );

		if ( ! is_array( $findings ) ) {
			return 0;
		}

		$dismissed = get_option( 'wpshadow_dismissed_findings', array() );
		$excluded  = get_option( 'wpshadow_excluded_findings', array() );

		$count = 0;
		foreach ( $findings as $key => $finding ) {
			if ( ! is_array( $finding ) ) {
				continue;
			}

			$finding_id = $finding['id'] ?? $key;
			if ( empty( $finding_id ) ) {
				continue;
			}

			if ( isset( $dismissed[ $finding_id ] ) || isset( $excluded[ $finding_id ] ) ) {
				continue;
			}

			++$count;
		}

		return $count;
	}

	/**
	 * Get the last diagnostic scan timestamp.
	 *
	 * @since 0.6095
	 * @return int Unix timestamp.
	 */
	private static function get_last_scan_timestamp(): int {
		$last_scan = get_option( 'wpshadow_last_quick_checks', 0 );

		return is_numeric( $last_scan ) ? (int) $last_scan : 0;
	}
}
