<?php declare(strict_types=1);
/**
 * Feature: Initial WordPress Setup Checker
 *
 * Guides users through essential WordPress setup steps: user account,
 * site name, timezone, permalinks, and search engine indexing.
 * Runs routine checks to ensure optimal configuration.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_Setup_Checks extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'setup-checks',
			'name'        => __( 'Initial Setup Checklist', 'wpshadow' ),
			'description' => __( 'Guide for essential WordPress configuration: user account, site name, timezone, permalinks, and search visibility.', 'wpshadow' ),
			'aliases'     => array( 'setup', 'onboarding', 'configuration', 'initial setup', 'wordpress setup', 'site setup', 'first time setup', 'setup checklist', 'configuration guide', 'wordpress guide' ),
			'sub_features' => array(
				'check_admin_user'       => __( 'Verify admin account is secure', 'wpshadow' ),
				'check_site_name'        => __( 'Verify site title and tagline', 'wpshadow' ),
				'check_timezone'         => __( 'Verify timezone matches server', 'wpshadow' ),
				'check_permalinks'       => __( 'Verify pretty URL structure', 'wpshadow' ),
				'check_search_indexing'  => __( 'Verify search engines can index site', 'wpshadow' ),
				'check_admin_email'      => __( 'Verify admin email is correct', 'wpshadow' ),
				'show_setup_wizard'      => __( 'Show setup wizard on first visit', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'check_admin_user'       => true,
			'check_site_name'        => true,
			'check_timezone'         => true,
			'check_permalinks'       => true,
			'check_search_indexing'  => true,
			'check_admin_email'      => true,
			'show_setup_wizard'      => true,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Show setup wizard on first visit
		if ( $this->is_sub_feature_enabled( 'show_setup_wizard', true ) && ! get_transient( 'wpshadow_setup_wizard_shown' ) ) {
			add_action( 'admin_notices', array( $this, 'show_setup_wizard_notice' ) );
			set_transient( 'wpshadow_setup_wizard_shown', 1, WEEK_IN_SECONDS );
		}

		// Run routine checks daily
		add_action( 'wp_scheduled_delete', array( $this, 'run_routine_checks' ) );

		// AJAX handlers
		add_action( 'wp_ajax_wpshadow_dismiss_setup_notice', array( $this, 'ajax_dismiss_setup_notice' ) );
		add_action( 'wp_ajax_wpshadow_run_setup_checks', array( $this, 'ajax_run_setup_checks' ) );

		// Site Health integration
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		// WP-CLI command
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow setup-checks', array( $this, 'handle_cli_command' ) );
		}
	}

	/**
	 * Show setup wizard notice.
	 */
	public function show_setup_wizard_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$results = $this->run_all_checks();
		$issues = $this->count_issues( $results );

		if ( $issues === 0 ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible" id="wpshadow-setup-wizard-notice">
			<p>
				<strong><?php esc_html_e( 'WPShadow Setup Guide', 'wpshadow' ); ?></strong>
			</p>
			<p>
				<?php
				printf(
					esc_html( _n( '%d setup item to review', '%d setup items to review', $issues, 'wpshadow' ) ),
					intval( $issues )
				);
				?>
			</p>
			<p>
				<a href="#" class="button button-primary" id="wpshadow-open-setup-wizard">
					<?php esc_html_e( 'Open Setup Guide', 'wpshadow' ); ?>
				</a>
				<a href="#" class="button" id="wpshadow-dismiss-setup-wizard">
					<?php esc_html_e( 'Dismiss', 'wpshadow' ); ?>
				</a>
			</p>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const dismissBtn = document.getElementById('wpshadow-dismiss-setup-wizard');
			if (dismissBtn) {
				dismissBtn.addEventListener('click', function(e) {
					e.preventDefault();
					fetch(ajaxurl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams({
							action: 'wpshadow_dismiss_setup_notice',
							nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_setup' ) ); ?>'
						})
					});
					document.getElementById('wpshadow-setup-wizard-notice').remove();
				});
			}

			const openBtn = document.getElementById('wpshadow-open-setup-wizard');
			if (openBtn) {
				openBtn.addEventListener('click', function(e) {
					e.preventDefault();
					<?php do_action( 'wpshadow_open_setup_wizard' ); ?>
				});
			}
		});
		</script>
		<?php
	}

	/**
	 * Run all setup checks.
	 */
	private function run_all_checks(): array {
		$results = array();

		if ( $this->is_sub_feature_enabled( 'check_admin_user', true ) ) {
			$results['admin_user'] = $this->check_admin_user();
		}

		if ( $this->is_sub_feature_enabled( 'check_site_name', true ) ) {
			$results['site_name'] = $this->check_site_name();
		}

		if ( $this->is_sub_feature_enabled( 'check_timezone', true ) ) {
			$results['timezone'] = $this->check_timezone();
		}

		if ( $this->is_sub_feature_enabled( 'check_permalinks', true ) ) {
			$results['permalinks'] = $this->check_permalinks();
		}

		if ( $this->is_sub_feature_enabled( 'check_search_indexing', true ) ) {
			$results['search_indexing'] = $this->check_search_indexing();
		}

		if ( $this->is_sub_feature_enabled( 'check_admin_email', true ) ) {
			$results['admin_email'] = $this->check_admin_email();
		}

		return $results;
	}

	/**
	 * Check admin user exists and is not default.
	 */
	private function check_admin_user(): array {
		$admin_user = get_user_by( 'login', 'admin' );

		if ( ! $admin_user ) {
			return array(
				'status'    => 'good',
				'message'   => __( 'Default admin user does not exist (good security).', 'wpshadow' ),
				'fix'       => null,
			);
		}

		return array(
			'status'    => 'warning',
			'message'   => __( 'Default "admin" user account exists. Consider deleting it for better security.', 'wpshadow' ),
			'fix'       => admin_url( 'user-edit.php?user_id=' . $admin_user->ID ),
			'fix_label' => __( 'Review Admin User', 'wpshadow' ),
		);
	}

	/**
	 * Check site name is configured.
	 */
	private function check_site_name(): array {
		$site_title = get_bloginfo( 'name' );
		$site_description = get_bloginfo( 'description' );

		$issues = array();

		if ( empty( $site_title ) || $site_title === 'Just another WordPress site' ) {
			$issues[] = __( 'Site title is not set or is default.', 'wpshadow' );
		}

		if ( empty( $site_description ) || $site_description === 'Just another WordPress site' ) {
			$issues[] = __( 'Site tagline/description is not set or is default.', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return array(
				'status'    => 'good',
				'message'   => __( 'Site title and tagline are configured.', 'wpshadow' ),
				'fix'       => null,
			);
		}

		return array(
			'status'    => 'warning',
			'message'   => implode( ' ', $issues ),
			'fix'       => admin_url( 'options-general.php' ),
			'fix_label' => __( 'Update Site Settings', 'wpshadow' ),
		);
	}

	/**
	 * Check timezone matches server.
	 */
	private function check_timezone(): array {
		$wp_tz = get_option( 'timezone_string' );
		$gmt_offset = get_option( 'gmt_offset' );

		if ( empty( $wp_tz ) && '0' !== $gmt_offset ) {
			return array(
				'status'    => 'warning',
				'message'   => __( 'Timezone not configured. Using UTC offset instead of named timezone.', 'wpshadow' ),
				'fix'       => admin_url( 'options-general.php' ),
				'fix_label' => __( 'Configure Timezone', 'wpshadow' ),
			);
		}

		if ( empty( $wp_tz ) ) {
			return array(
				'status'    => 'good',
				'message'   => __( 'Timezone is set to UTC.', 'wpshadow' ),
				'fix'       => null,
			);
		}

		return array(
			'status'    => 'good',
			'message'   => sprintf( __( 'Timezone configured: %s', 'wpshadow' ), $wp_tz ),
			'fix'       => null,
		);
	}

	/**
	 * Check permalinks are configured.
	 */
	private function check_permalinks(): array {
		$permalink_structure = get_option( 'permalink_structure' );

		if ( empty( $permalink_structure ) ) {
			return array(
				'status'    => 'warning',
				'message'   => __( 'Pretty URLs (permalinks) are not enabled. Using default URLs.', 'wpshadow' ),
				'fix'       => admin_url( 'options-permalink.php' ),
				'fix_label' => __( 'Configure Permalinks', 'wpshadow' ),
			);
		}

		// Check if it's the default structure
		if ( '/%post_id%/' === $permalink_structure ) {
			return array(
				'status'    => 'recommended',
				'message'   => __( 'Using default numeric permalink structure. Consider using post name for better SEO.', 'wpshadow' ),
				'fix'       => admin_url( 'options-permalink.php' ),
				'fix_label' => __( 'Configure Permalinks', 'wpshadow' ),
			);
		}

		return array(
			'status'    => 'good',
			'message'   => sprintf( __( 'Pretty URLs enabled: %s', 'wpshadow' ), $permalink_structure ),
			'fix'       => null,
		);
	}

	/**
	 * Check search engine indexing is enabled.
	 */
	private function check_search_indexing(): array {
		$discourage_search = get_option( 'blog_public' );

		if ( '0' === $discourage_search ) {
			return array(
				'status'    => 'critical',
				'message'   => __( 'Search engines are blocked from indexing this site! You may have accidentally disabled this during development.', 'wpshadow' ),
				'fix'       => admin_url( 'options-reading.php' ),
				'fix_label' => __( 'Enable Search Engines', 'wpshadow' ),
			);
		}

		return array(
			'status'    => 'good',
			'message'   => __( 'Search engines can index this site.', 'wpshadow' ),
			'fix'       => null,
		);
	}

	/**
	 * Check admin email is configured.
	 */
	private function check_admin_email(): array {
		$admin_email = get_option( 'admin_email' );

		if ( empty( $admin_email ) || false === filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) ) {
			return array(
				'status'    => 'critical',
				'message'   => __( 'Admin email is not valid. You will not receive important notifications.', 'wpshadow' ),
				'fix'       => admin_url( 'options-general.php' ),
				'fix_label' => __( 'Update Admin Email', 'wpshadow' ),
			);
		}

		return array(
			'status'    => 'good',
			'message'   => sprintf( __( 'Admin email: %s', 'wpshadow' ), $admin_email ),
			'fix'       => null,
		);
	}

	/**
	 * Run routine checks.
	 */
	public function run_routine_checks(): void {
		$results = $this->run_all_checks();
		set_transient( 'wpshadow_setup_checks_results', $results, DAY_IN_SECONDS );

		// Log critical issues
		foreach ( $results as $check => $result ) {
			if ( 'critical' === $result['status'] ) {
				$this->log_activity(
					'Setup Check - Critical',
					sprintf( 'Critical setup issue: %s - %s', $check, $result['message'] ),
					'error'
				);
			}
		}

		do_action( 'wpshadow_setup_checks_completed', $results );
	}

	/**
	 * Count issues in results.
	 */
	private function count_issues( array $results ): int {
		$count = 0;
		foreach ( $results as $result ) {
			if ( 'good' !== $result['status'] ) {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * AJAX: Dismiss setup notice.
	 */
	public function ajax_dismiss_setup_notice(): void {
		check_ajax_referer( 'wpshadow_setup' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		delete_transient( 'wpshadow_setup_wizard_shown' );
		wp_send_json_success( array( 'msg' => __( 'Setup wizard dismissed', 'wpshadow' ) ) );
	}

	/**
	 * AJAX: Run setup checks on demand.
	 */
	public function ajax_run_setup_checks(): void {
		check_ajax_referer( 'wpshadow_setup' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		$results = $this->run_all_checks();
		wp_send_json_success( $results );
	}

	/**
	 * Register Site Health test.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['setup_checks'] = array(
			'label'  => __( 'Initial Setup', 'wpshadow' ),
			'test'   => array( $this, 'test_setup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for setup checks.
	 */
	public function test_setup(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Initial Setup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable setup checks to verify WordPress configuration.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'setup_checks',
			);
		}

		$results = get_transient( 'wpshadow_setup_checks_results' );
		if ( false === $results ) {
			$results = $this->run_all_checks();
		}

		$critical = 0;
		$warnings = 0;

		foreach ( $results as $result ) {
			if ( 'critical' === $result['status'] ) {
				$critical++;
			} elseif ( 'warning' === $result['status'] || 'recommended' === $result['status'] ) {
				$warnings++;
			}
		}

		$status = 'good';
		$message = __( 'Setup checklist: All items configured correctly.', 'wpshadow' );

		if ( $critical > 0 ) {
			$status = 'critical';
			$message = sprintf(
				_n( '%d critical setup issue detected.', '%d critical setup issues detected.', $critical, 'wpshadow' ),
				$critical
			);
		} elseif ( $warnings > 0 ) {
			$status = 'recommended';
			$message = sprintf(
				_n( '%d setup recommendation.', '%d setup recommendations.', $warnings, 'wpshadow' ),
				$warnings
			);
		}

		return array(
			'label'       => __( 'Initial Setup', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => $message,
			'actions'     => '',
			'test'        => 'setup_checks',
		);
	}

	/**
	 * Handle WP-CLI command for setup checks.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 *
	 * @return void
	 */
	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' === $action ) {
			\WP_CLI::log( __( 'Running setup checks...', 'wpshadow' ) );
			$results = $this->run_all_checks();

			foreach ( $results as $check => $result ) {
				$symbol = match( $result['status'] ) {
					'good'        => '✓',
					'warning'     => '⚠',
					'recommended' => '→',
					'critical'    => '✗',
					default       => '?',
				};

				\WP_CLI::log( sprintf(
					'  %s %s: %s',
					$symbol,
					ucfirst( str_replace( '_', ' ', $check ) ),
					$result['message']
				) );

				if ( ! empty( $result['fix'] ) ) {
					\WP_CLI::log( sprintf( '    → %s', $result['fix'] ) );
				}
			}

			\WP_CLI::success( __( 'Setup checks completed.', 'wpshadow' ) );
		} elseif ( 'run' === $action ) {
			$this->run_routine_checks();
			\WP_CLI::success( __( 'Setup checks queued for background processing.', 'wpshadow' ) );
		} else {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow setup-checks status', 'wpshadow' ) );
		}
	}
}
