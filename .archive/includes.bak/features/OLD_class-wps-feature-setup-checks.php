<?php declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Setup_Checks extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'setup-checks',
			'name'        => __( 'Initial Setup Checklist', 'wpshadow' ),
			'description' => __( 'Guide for essential WordPress configuration: user account, site name, timezone, permalinks, and search visibility.', 'wpshadow' ),
			'aliases'     => array( 'setup', 'onboarding', 'configuration', 'initial setup', 'wordpress setup', 'site setup', 'first time setup', 'setup checklist', 'configuration guide', 'wordpress guide' ),
			'sub_features' => array(
				'check_admin_user'       => array(
					'name'               => __( 'Admin Account Security', 'wpshadow' ),
					'description_short'  => __( 'Verify admin account uses secure username', 'wpshadow' ),
					'description_long'   => __( 'Checks that your main admin account doesn\'t use the default "admin" username, which is a major security vulnerability. Default admin usernames are brute-force targets. If using "admin", this warning recommends creating a new admin and deleting the default account.', 'wpshadow' ),
					'description_wizard' => __( 'Using "admin" as your username is a massive security risk. This checks and alerts you to change it to something random.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'check_site_name'        => array(
					'name'               => __( 'Site Title Setup', 'wpshadow' ),
					'description_short'  => __( 'Verify site title and tagline are set', 'wpshadow' ),
					'description_long'   => __( 'Checks that your site has a meaningful title and tagline configured. These appear in browser tabs, search results, and feeds. Many new sites are left with default values like "Just another WordPress site". This helps you set proper branding information.', 'wpshadow' ),
					'description_wizard' => __( 'Your site title and tagline are important for branding and SEO. This checks if you\'ve set them up properly.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'check_timezone'         => array(
					'name'               => __( 'Timezone Configuration', 'wpshadow' ),
					'description_short'  => __( 'Verify timezone matches your server', 'wpshadow' ),
					'description_long'   => __( 'Checks that your WordPress timezone setting matches your server timezone. Mismatched timezones cause scheduling issues with posts, backups, and cron tasks. Post publication times appear wrong and automated tasks run at unexpected times. This helps ensure correct time handling.', 'wpshadow' ),
					'description_wizard' => __( 'Timezone mismatch causes scheduled posts and backups to run at wrong times. Verify they match.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'check_permalinks'       => array(
					'name'               => __( 'Permalink Structure', 'wpshadow' ),
					'description_short'  => __( 'Verify pretty URLs are enabled', 'wpshadow' ),
					'description_long'   => __( 'Checks that permalinks are configured with a pretty structure (like /2024/01/post-title/ instead of /?p=123). Pretty permalinks are much better for SEO and user experience. Default plain URLs hurt search ranking and look ugly in addresses.', 'wpshadow' ),
					'description_wizard' => __( 'Pretty URLs are essential for SEO and user experience. Most new sites still use plain URLs like /?p=123. This reminds you to enable pretty URLs.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'check_search_indexing'  => array(
					'name'               => __( 'Search Engine Indexing', 'wpshadow' ),
					'description_short'  => __( 'Verify search engines can index your site', 'wpshadow' ),
					'description_long'   => __( 'Checks the "Discourage search engines from indexing this site" setting. Some sites accidentally enable this and then wonder why they don\'t appear in Google. This setting is sometimes enabled during development and forgotten. This check alerts you if it\'s accidentally enabled on a live site.', 'wpshadow' ),
					'description_wizard' => __( 'Accidentally disabling search indexing is common during testing. This checks if your live site is accidentally hidden from Google.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'check_admin_email'      => array(
					'name'               => __( 'Admin Email Address', 'wpshadow' ),
					'description_short'  => __( 'Verify admin email is correct', 'wpshadow' ),
					'description_long'   => __( 'Checks that your admin email address is correctly configured and monitored. WordPress sends important notifications to this address (new users, security alerts, updates). If the email is wrong or unmonitored, you might miss critical information about your site.', 'wpshadow' ),
					'description_wizard' => __( 'WordPress uses the admin email for important notifications. Verify it\'s a real, monitored email address.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'show_setup_wizard'      => array(
					'name'               => __( 'Show Setup Wizard', 'wpshadow' ),
					'description_short'  => __( 'Display setup checklist on first admin visit', 'wpshadow' ),
					'description_long'   => __( 'Shows an admin notice with a setup wizard checklist when admin first visits the dashboard. The wizard guides through essential configuration steps. Disabled by default to avoid nagging experienced users.', 'wpshadow' ),
					'description_wizard' => __( 'Show setup checklist to new site administrators. Helpful for guiding through initial configuration steps.', 'wpshadow' ),
					'default_enabled'    => true,
				),
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

		if ( $this->is_sub_feature_enabled( 'show_setup_wizard', true ) && ! get_transient( 'wpshadow_setup_wizard_shown' ) ) {
			add_action( 'admin_notices', array( $this, 'show_setup_wizard_notice' ) );
			set_transient( 'wpshadow_setup_wizard_shown', 1, WEEK_IN_SECONDS );
		}

		add_action( 'wp_scheduled_delete', array( $this, 'run_routine_checks' ) );

		add_action( 'wp_ajax_wpshadow_dismiss_setup_notice', array( $this, 'ajax_dismiss_setup_notice' ) );
		add_action( 'wp_ajax_wpshadow_run_setup_checks', array( $this, 'ajax_run_setup_checks' ) );

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow setup-checks', array( $this, 'handle_cli_command' ) );
		}
	}

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

	public function run_routine_checks(): void {
		$results = $this->run_all_checks();
		set_transient( 'wpshadow_setup_checks_results', $results, DAY_IN_SECONDS );

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

	private function count_issues( array $results ): int {
		$count = 0;
		foreach ( $results as $result ) {
			if ( 'good' !== $result['status'] ) {
				$count++;
			}
		}
		return $count;
	}

	public function ajax_dismiss_setup_notice(): void {
		check_ajax_referer( 'wpshadow_setup' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		delete_transient( 'wpshadow_setup_wizard_shown' );
		wp_send_json_success( array( 'msg' => __( 'Setup wizard dismissed', 'wpshadow' ) ) );
	}

	public function ajax_run_setup_checks(): void {
		check_ajax_referer( 'wpshadow_setup' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		$results = $this->run_all_checks();
		wp_send_json_success( $results );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['setup_checks'] = array(
			'label'  => __( 'Initial Setup', 'wpshadow' ),
			'test'   => array( $this, 'test_setup' ),
		);

		return $tests;
	}

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
