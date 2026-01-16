<?php
/**
 * WPS Troubleshooting Wizard
 *
 * Intelligent problem solver that guides users through common WordPress issues
 * with smart detection and one-click fixes.
 *
 * @package WPSHADOW_WP_SUPPORT
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPSHADOW_Troubleshooting_Wizard
 *
 * Provides conversational step-by-step problem solver with:
 * - 10+ common issue categories
 * - Smart error log detection
 * - Guided remediation steps
 * - One-click fixes
 * - System report integration
 */
class WPSHADOW_Troubleshooting_Wizard {

	/**
	 * Database option key for wizard sessions.
	 */
	private const SESSION_KEY = 'wpshadow_troubleshooting_session';

	/**
	 * Database option key for issue history.
	 */
	private const HISTORY_KEY = 'wpshadow_troubleshooting_history';

	/**
	 * Issue categories and their patterns.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static $issue_categories = array();

	/**
	 * Initialize troubleshooting wizard.
	 *
	 * @return void
	 */
	public static function init(): void {
		self::register_issue_categories();

		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_WPSHADOW_start_troubleshooting', array( __CLASS__, 'ajax_start_troubleshooting' ) );
		add_action( 'wp_ajax_WPSHADOW_analyze_issue', array( __CLASS__, 'ajax_analyze_issue' ) );
		add_action( 'wp_ajax_WPSHADOW_apply_fix', array( __CLASS__, 'ajax_apply_fix' ) );
		add_action( 'wp_ajax_WPSHADOW_generate_support_report', array( __CLASS__, 'ajax_generate_support_report' ) );
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'add_dashboard_widget' ) );
	}

	/**
	 * Register all available issue categories.
	 *
	 * @return void
	 */
	private static function register_issue_categories(): void {
		self::$issue_categories = array(
			'white_screen'      => array(
				'title'       => __( 'White Screen of Death', 'plugin-wpshadow' ),
				'description' => __( 'Site shows blank page or error', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-warning',
				'patterns'    => array( 'Fatal error', 'syntax error', 'parse error', 'allowed memory size', 'maximum execution time' ),
				'checks'      => array( 'check_error_logs', 'check_memory_limit', 'check_php_version' ),
				'fixes'       => array( 'enable_debug', 'increase_memory', 'disable_plugins', 'switch_theme' ),
			),
			'login_issues'      => array(
				'title'       => __( 'Login Issues', 'plugin-wpshadow' ),
				'description' => __( 'Login page not working or redirecting', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-lock',
				'patterns'    => array( 'wp-login.php', 'redirect loop', 'cookies are blocked', 'invalid username', 'too many redirects' ),
				'checks'      => array( 'check_wp_config', 'check_htaccess', 'check_cookies' ),
				'fixes'       => array( 'clear_cookies', 'reset_permalinks', 'check_site_url', 'disable_plugins' ),
			),
			'plugin_error'      => array(
				'title'       => __( 'Plugin Error', 'plugin-wpshadow' ),
				'description' => __( 'Plugin causing issues or conflicts', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-admin-plugins',
				'patterns'    => array( 'Call to undefined function', 'Call to undefined method', 'plugin', 'conflict', 'incompatible' ),
				'checks'      => array( 'check_error_logs', 'check_plugin_compatibility' ),
				'fixes'       => array( 'disable_plugins', 'update_plugins', 'safe_mode' ),
			),
			'slow_performance'  => array(
				'title'       => __( 'Slow Performance', 'plugin-wpshadow' ),
				'description' => __( 'Site loading slowly or timing out', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-dashboard',
				'patterns'    => array( 'slow query', 'timeout', 'max_execution_time', 'memory_limit' ),
				'checks'      => array( 'check_database_queries', 'check_cache', 'check_plugins_load' ),
				'fixes'       => array( 'enable_cache', 'optimize_database', 'disable_heavy_plugins' ),
			),
			'upload_fails'      => array(
				'title'       => __( 'Upload Fails', 'plugin-wpshadow' ),
				'description' => __( 'Trouble uploading images or files', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-upload',
				'patterns'    => array( 'upload', 'post_max_size', 'upload_max_filesize', 'permission denied', 'failed to write' ),
				'checks'      => array( 'check_upload_limits', 'check_directory_permissions' ),
				'fixes'       => array( 'increase_upload_limits', 'fix_permissions', 'check_disk_space' ),
			),
			'email_issues'      => array(
				'title'       => __( 'Email Not Working', 'plugin-wpshadow' ),
				'description' => __( 'WordPress not sending emails', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-email',
				'patterns'    => array( 'wp_mail', 'mail() failed', 'SMTP', 'email', 'sendmail' ),
				'checks'      => array( 'check_mail_function', 'check_smtp_settings' ),
				'fixes'       => array( 'test_email', 'configure_smtp', 'check_spam_folder' ),
			),
			'database_error'    => array(
				'title'       => __( 'Database Error', 'plugin-wpshadow' ),
				'description' => __( 'Database connection or query issues', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-database',
				'patterns'    => array( 'Error establishing a database connection', 'database', 'MySQL', 'query', 'table doesn\'t exist' ),
				'checks'      => array( 'check_database_connection', 'check_database_tables' ),
				'fixes'       => array( 'repair_database', 'check_credentials', 'optimize_tables' ),
			),
			'theme_issues'      => array(
				'title'       => __( 'Theme Problems', 'plugin-wpshadow' ),
				'description' => __( 'Theme causing display or functionality issues', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-admin-appearance',
				'patterns'    => array( 'theme', 'template', 'stylesheet', 'header', 'footer' ),
				'checks'      => array( 'check_theme_files', 'check_theme_compatibility' ),
				'fixes'       => array( 'switch_theme', 'update_theme', 'clear_cache' ),
			),
			'security_concerns' => array(
				'title'       => __( 'Security Concerns', 'plugin-wpshadow' ),
				'description' => __( 'Suspicious activity or vulnerabilities', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-shield',
				'patterns'    => array( 'hack', 'malware', 'unauthorized', 'suspicious', 'injection' ),
				'checks'      => array( 'check_file_changes', 'check_user_accounts', 'check_activity_logs' ),
				'fixes'       => array( 'scan_files', 'review_users', 'update_passwords', 'enable_2fa' ),
			),
			'update_failures'   => array(
				'title'       => __( 'Update Failed', 'plugin-wpshadow' ),
				'description' => __( 'WordPress, plugin, or theme update issues', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-update',
				'patterns'    => array( 'update failed', 'download failed', 'installation failed', 'version', 'upgrade' ),
				'checks'      => array( 'check_file_permissions', 'check_disk_space', 'check_connection' ),
				'fixes'       => array( 'fix_permissions', 'clear_update_cache', 'manual_update' ),
			),
		);
	}

	/**
	 * Register admin page for troubleshooting wizard.
	 *
	 * @return void
	 */
	public static function register_admin_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Troubleshooting Wizard', 'plugin-wpshadow' ),
			__( 'Troubleshoot', 'plugin-wpshadow' ),
			'manage_options',
			'wpshadow-troubleshoot',
			array( __CLASS__, 'render_wizard_page' )
		);
	}

	/**
	 * Enqueue wizard assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( 'support_page_wpshadow-troubleshoot' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wps-troubleshooting-wizard',
			plugin_dir_url( __DIR__ . '/../' ) . 'assets/css/troubleshooting-wizard.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'wps-troubleshooting-wizard',
			plugin_dir_url( __DIR__ . '/../' ) . 'assets/js/troubleshooting-wizard.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'wps-troubleshooting-wizard',
			'wpsWizard',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wpshadow_troubleshoot_nonce' ),
			)
		);
	}

	/**
	 * Render troubleshooting wizard page.
	 *
	 * @return void
	 */
	public static function render_wizard_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
		}

		$session = get_transient( self::SESSION_KEY . '_' . get_current_user_id() );
		?>
<div class="wrap wps-troubleshooting-wizard">
<h1><?php esc_html_e( 'Troubleshooting Wizard', 'plugin-wpshadow' ); ?></h1>
<p class="description">
		<?php esc_html_e( 'Let\'s solve your WordPress problem step by step with smart detection and one-click fixes.', 'plugin-wpshadow' ); ?>
</p>

<div class="wps-wizard-container">
		<?php if ( empty( $session ) ) : ?>
			<?php self::render_issue_selection(); ?>
<?php else : ?>
	<?php self::render_diagnosis_flow( $session ); ?>
<?php endif; ?>
</div>
</div>
		<?php
	}

	/**
	 * Render issue selection screen.
	 *
	 * @return void
	 */
	private static function render_issue_selection(): void {
		?>
<div class="wps-wizard-step wps-step-selection" data-step="selection">
<h2><?php esc_html_e( 'What\'s happening with your site?', 'plugin-wpshadow' ); ?></h2>

<div class="wps-issue-grid">
		<?php foreach ( self::$issue_categories as $key => $category ) : ?>
<div class="wps-issue-card" data-issue="<?php echo esc_attr( $key ); ?>">
<span class="dashicons <?php echo esc_attr( $category['icon'] ); ?>"></span>
<h3><?php echo esc_html( $category['title'] ); ?></h3>
<p><?php echo esc_html( $category['description'] ); ?></p>
<button type="button" class="button button-primary wps-select-issue">
			<?php esc_html_e( 'Diagnose This', 'plugin-wpshadow' ); ?>
</button>
</div>
<?php endforeach; ?>
</div>

<div class="wps-wizard-footer">
<p>
		<?php
		printf(
		/* translators: %s: Link to support */
			esc_html__( 'Don\'t see your issue? %s', 'plugin-wpshadow' ),
			'<a href="' . esc_url( admin_url( 'admin.php?page=wpshadow&tab=help' ) ) . '">' . esc_html__( 'Contact Professional Support', 'plugin-wpshadow' ) . '</a>'
		);
		?>
</p>
</div>
</div>
		<?php
	}

	/**
	 * Render diagnosis flow.
	 *
	 * @param array $session Current session data.
	 * @return void
	 */
	private static function render_diagnosis_flow( array $session ): void {
		$issue    = $session['issue'] ?? '';
		$category = self::$issue_categories[ $issue ] ?? array();

		if ( empty( $category ) ) {
			self::render_issue_selection();
			return;
		}

		$step = $session['step'] ?? 'diagnosis';
		?>
<div class="wps-wizard-step wps-step-<?php echo esc_attr( $step ); ?>" data-step="<?php echo esc_attr( $step ); ?>">
<div class="wps-wizard-progress">
<div class="wps-progress-step <?php echo 'diagnosis' === $step ? 'active' : 'completed'; ?>">
<span class="step-number">1</span>
<span class="step-label"><?php esc_html_e( 'Diagnosis', 'plugin-wpshadow' ); ?></span>
</div>
<div class="wps-progress-step <?php echo 'fixes' === $step ? 'active' : ( 'support' === $step ? 'completed' : '' ); ?>">
<span class="step-number">2</span>
<span class="step-label"><?php esc_html_e( 'Suggested Fixes', 'plugin-wpshadow' ); ?></span>
</div>
<div class="wps-progress-step <?php echo 'support' === $step ? 'active' : ''; ?>">
<span class="step-number">3</span>
<span class="step-label"><?php esc_html_e( 'Get Support', 'plugin-wpshadow' ); ?></span>
</div>
</div>

<h2>
<span class="dashicons <?php echo esc_attr( $category['icon'] ); ?>"></span>
		<?php echo esc_html( $category['title'] ); ?>
</h2>

<div class="wps-wizard-content">
		<?php
		switch ( $step ) {
			case 'diagnosis':
				self::render_diagnosis_step( $issue, $category );
				break;
			case 'fixes':
				self::render_fixes_step( $issue, $category, $session );
				break;
			case 'support':
				self::render_support_step( $issue, $category, $session );
				break;
		}
		?>
</div>

<div class="wps-wizard-actions">
<button type="button" class="button wps-restart-wizard">
		<?php esc_html_e( 'Start Over', 'plugin-wpshadow' ); ?>
</button>
</div>
</div>
		<?php
	}

	/**
	 * Render diagnosis step.
	 *
	 * @param string $issue    Issue key.
	 * @param array  $category Issue category data.
	 * @return void
	 */
	private static function render_diagnosis_step( string $issue, array $category ): void {
		?>
<div class="wps-diagnosis-panel">
<div class="wps-diagnosis-loading">
<span class="spinner is-active"></span>
<p><?php esc_html_e( 'Analyzing your WordPress site...', 'plugin-wpshadow' ); ?></p>
</div>

<div class="wps-diagnosis-results" style="display: none;">
<!-- Results will be populated via AJAX -->
</div>

<button type="button" class="button button-primary wps-view-fixes" style="display: none;">
		<?php esc_html_e( 'View Suggested Fixes', 'plugin-wpshadow' ); ?>
</button>
</div>
		<?php
	}

	/**
	 * Render fixes step.
	 *
	 * @param string $issue    Issue key.
	 * @param array  $category Issue category data.
	 * @param array  $session  Session data.
	 * @return void
	 */
	private static function render_fixes_step( string $issue, array $category, array $session ): void {
		$findings = $session['findings'] ?? array();
		?>
<div class="wps-fixes-panel">
<h3><?php esc_html_e( 'Recommended Solutions', 'plugin-wpshadow' ); ?></h3>

		<?php if ( ! empty( $findings ) ) : ?>
<div class="wps-findings-summary">
<h4><?php esc_html_e( 'What We Found:', 'plugin-wpshadow' ); ?></h4>
<ul>
			<?php foreach ( $findings as $finding ) : ?>
<li>
<span class="dashicons dashicons-<?php echo esc_attr( $finding['severity'] === 'critical' ? 'warning' : 'info' ); ?>"></span>
				<?php echo esc_html( $finding['message'] ); ?>
</li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<div class="wps-available-fixes">
<!-- Fixes will be populated based on diagnosis -->
</div>

<div class="wps-wizard-footer">
<p>
		<?php esc_html_e( 'Still having issues after trying these fixes?', 'plugin-wpshadow' ); ?>
<button type="button" class="button wps-need-support">
		<?php esc_html_e( 'Generate Support Report', 'plugin-wpshadow' ); ?>
</button>
</p>
</div>
</div>
		<?php
	}

	/**
	 * Render support step.
	 *
	 * @param string $issue    Issue key.
	 * @param array  $category Issue category data.
	 * @param array  $session  Session data.
	 * @return void
	 */
	private static function render_support_step( string $issue, array $category, array $session ): void {
		?>
<div class="wps-support-panel">
<h3><?php esc_html_e( 'Professional Support', 'plugin-wpshadow' ); ?></h3>

<p><?php esc_html_e( 'We\'ve generated a comprehensive report about your issue. This report includes:', 'plugin-wpshadow' ); ?></p>

<ul class="wps-report-contents">
<li><?php esc_html_e( 'System information and environment details', 'plugin-wpshadow' ); ?></li>
<li><?php esc_html_e( 'Error logs and diagnostic findings', 'plugin-wpshadow' ); ?></li>
<li><?php esc_html_e( 'Steps taken and fixes attempted', 'plugin-wpshadow' ); ?></li>
<li><?php esc_html_e( 'Plugin and theme information', 'plugin-wpshadow' ); ?></li>
</ul>

<div class="wps-report-download">
<button type="button" class="button button-primary wps-download-report">
<span class="dashicons dashicons-download"></span>
		<?php esc_html_e( 'Download Support Report', 'plugin-wpshadow' ); ?>
</button>
</div>

<div class="wps-contact-support">
<h4><?php esc_html_e( 'Contact Support', 'plugin-wpshadow' ); ?></h4>
<p><?php esc_html_e( 'Share this report with your support team or WordPress developer.', 'plugin-wpshadow' ); ?></p>
<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&tab=help' ) ); ?>" class="button button-secondary">
		<?php esc_html_e( 'Go to Support Page', 'plugin-wpshadow' ); ?>
</a>
</div>
</div>
		<?php
	}

	/**
	 * AJAX: Start troubleshooting session.
	 *
	 * @return void
	 */
	public static function ajax_start_troubleshooting(): void {
		check_ajax_referer( 'wpshadow_troubleshoot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			\WPShadow\WPSHADOW_ajax_permission_denied();
		}

		$issue = \WPShadow\WPSHADOW_get_post_text( 'issue' );

		if ( empty( $issue ) || ! isset( self::$issue_categories[ $issue ] ) ) {
			\WPShadow\WPSHADOW_ajax_invalid_request( 'issue' );
		}

		$session = array(
			'issue'      => $issue,
			'step'       => 'diagnosis',
			'started_at' => time(),
			'findings'   => array(),
		);

		set_transient( self::SESSION_KEY . '_' . get_current_user_id(), $session, HOUR_IN_SECONDS );

		wp_send_json_success(
			array(
				'message'  => __( 'Troubleshooting session started.', 'plugin-wpshadow' ),
				'redirect' => admin_url( 'admin.php?page=wpshadow-troubleshoot' ),
			)
		);
	}

	/**
	 * AJAX: Analyze issue and provide findings.
	 *
	 * @return void
	 */
	public static function ajax_analyze_issue(): void {
		check_ajax_referer( 'wpshadow_troubleshoot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			\WPShadow\WPSHADOW_ajax_permission_denied();
		}

		$user_id = get_current_user_id();
		$session = get_transient( self::SESSION_KEY . '_' . $user_id );

		if ( empty( $session ) ) {
			\WPShadow\WPSHADOW_ajax_error( __( 'No active session found.', 'plugin-wpshadow' ) );
		}

		$issue    = $session['issue'];
		$category = self::$issue_categories[ $issue ] ?? array();

		if ( empty( $category ) ) {
			\WPShadow\WPSHADOW_ajax_error( __( 'That issue type doesn\'t exist.', 'plugin-wpshadow' ) );
		}

		// Perform analysis based on category checks.
		$findings = self::analyze_issue( $issue, $category );

		// Update session with findings.
		$session['findings']    = $findings;
		$session['analyzed_at'] = time();
		set_transient( self::SESSION_KEY . '_' . $user_id, $session, HOUR_IN_SECONDS );

		// Save to history.
		self::save_to_history( $issue, $findings );

		wp_send_json_success(
			array(
				'findings'     => $findings,
				'issue'        => $issue,
				'category'     => $category,
				'has_critical' => self::has_critical_findings( $findings ),
			)
		);
	}

	/**
	 * Analyze issue and return findings.
	 *
	 * @param string $issue    Issue key.
	 * @param array  $category Issue category data.
	 * @return array<array<string, mixed>>
	 */
	private static function analyze_issue( string $issue, array $category ): array {
		$findings = array();

		// Check error logs for patterns.
		$error_findings = self::check_error_logs( $category['patterns'] ?? array() );
		$findings       = array_merge( $findings, $error_findings );

		// Run category-specific checks.
		$checks = $category['checks'] ?? array();
		foreach ( $checks as $check_method ) {
			if ( method_exists( __CLASS__, $check_method ) ) {
				$check_findings = call_user_func( array( __CLASS__, $check_method ) );
				if ( ! empty( $check_findings ) ) {
					$findings = array_merge( $findings, $check_findings );
				}
			}
		}

		// If no specific findings, add general message.
		if ( empty( $findings ) ) {
			$findings[] = array(
				'severity' => 'info',
				'message'  => __( 'No specific issues detected. Proceeding with recommended fixes.', 'plugin-wpshadow' ),
				'details'  => '',
			);
		}

		return $findings;
	}

	/**
	 * Check error logs for patterns.
	 *
	 * @param array $patterns Error patterns to search for.
	 * @return array<array<string, mixed>>
	 */
	private static function check_error_logs( array $patterns ): array {
		$findings   = array();
		$error_logs = self::get_recent_error_logs();

		foreach ( $patterns as $pattern ) {
			foreach ( $error_logs as $log_entry ) {
				if ( stripos( $log_entry, $pattern ) !== false ) {
					$findings[] = array(
						'severity' => 'critical',
						'message'  => sprintf(
						/* translators: %s: Error pattern found */
							__( 'Found error pattern: %s', 'plugin-wpshadow' ),
							$pattern
						),
						'details'  => substr( $log_entry, 0, 200 ),
					);
					break; // Only report once per pattern.
				}
			}
		}

		return $findings;
	}

	/**
	 * Get recent error log entries.
	 *
	 * @return array<string>
	 */
	private static function get_recent_error_logs(): array {
		$logs      = array();
		$error_log = ini_get( 'error_log' );

		if ( empty( $error_log ) ) {
			$error_log = WP_CONTENT_DIR . '/debug.log';
		}

		if ( file_exists( $error_log ) && is_readable( $error_log ) ) {
			$content = file_get_contents( $error_log );
			if ( $content ) {
				$lines = explode( "\n", $content );
				$logs  = array_slice( $lines, -100 ); // Last 100 lines.
			}
		}

		return $logs;
	}

	// Additional check methods to be implemented...
	private static function check_memory_limit(): array {
		return array(); }
	private static function check_php_version(): array {
		return array(); }
	private static function check_wp_config(): array {
		return array(); }
	private static function check_htaccess(): array {
		return array(); }
	private static function check_cookies(): array {
		return array(); }
	private static function check_plugin_compatibility(): array {
		return array(); }
	private static function check_database_queries(): array {
		return array(); }
	private static function check_cache(): array {
		return array(); }
	private static function check_plugins_load(): array {
		return array(); }
	private static function check_upload_limits(): array {
		return array(); }
	private static function check_directory_permissions(): array {
		return array(); }
	private static function check_mail_function(): array {
		return array(); }
	private static function check_smtp_settings(): array {
		return array(); }
	private static function check_database_connection(): array {
		return array(); }
	private static function check_database_tables(): array {
		return array(); }
	private static function check_theme_files(): array {
		return array(); }
	private static function check_theme_compatibility(): array {
		return array(); }
	private static function check_file_changes(): array {
		return array(); }
	private static function check_user_accounts(): array {
		return array(); }
	private static function check_activity_logs(): array {
		return array(); }
	private static function check_file_permissions(): array {
		return array(); }
	private static function check_disk_space(): array {
		return array(); }
	private static function check_connection(): array {
		return array(); }

	/**
	 * Check if findings include critical issues.
	 *
	 * @param array $findings Analysis findings.
	 * @return bool
	 */
	private static function has_critical_findings( array $findings ): bool {
		foreach ( $findings as $finding ) {
			if ( 'critical' === $finding['severity'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Save troubleshooting session to history.
	 *
	 * @param string $issue    Issue key.
	 * @param array  $findings Analysis findings.
	 * @return void
	 */
	private static function save_to_history( string $issue, array $findings ): void {
		$history = get_option( self::HISTORY_KEY, array() );

		$history[] = array(
			'issue'     => $issue,
			'findings'  => $findings,
			'timestamp' => time(),
			'user_id'   => get_current_user_id(),
		);

		// Keep last 50 sessions using helper.
		$history = \WPShadow\WPSHADOW_limit_array_size( $history, 50 );

		update_option( self::HISTORY_KEY, $history );
	}

	/**
	 * AJAX: Apply a fix.
	 *
	 * @return void
	 */
	public static function ajax_apply_fix(): void {
		check_ajax_referer( 'wpshadow_troubleshoot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$fix = sanitize_text_field( wp_unslash( $_POST['fix'] ?? '' ) );

		if ( empty( $fix ) ) {
			wp_send_json_error( array( 'message' => __( 'That fix doesn\'t exist.', 'plugin-wpshadow' ) ) );
		}

		// Apply the fix based on type.
		$result = self::apply_fix( $fix );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Apply a specific fix.
	 *
	 * @param string $fix Fix identifier.
	 * @return array<string, mixed>
	 */
	private static function apply_fix( string $fix ): array {
		switch ( $fix ) {
			case 'enable_debug':
				return array(
					'success' => true,
					'message' => __( 'To enable debug mode, add these lines to wp-config.php before "That\'s all, stop editing!"', 'plugin-wpshadow' ),
					'code'    => "define( 'WP_DEBUG', true );\ndefine( 'WP_DEBUG_LOG', true );\ndefine( 'WP_DEBUG_DISPLAY', false );",
				);

			case 'disable_plugins':
				return array(
					'success' => true,
					'message' => __( 'Use the Emergency Recovery mode to safely disable all plugins.', 'plugin-wpshadow' ),
					'action'  => 'emergency_mode',
				);

			case 'switch_theme':
				return array(
					'success' => true,
					'message' => __( 'Consider switching to a default WordPress theme to test.', 'plugin-wpshadow' ),
					'action'  => 'change_theme',
				);

			case 'clear_cache':
				wp_cache_flush();
				return array(
					'success' => true,
					'message' => __( 'Cache cleared successfully.', 'plugin-wpshadow' ),
				);

			default:
				return array(
					'success' => false,
					'message' => __( 'Unknown fix action.', 'plugin-wpshadow' ),
				);
		}
	}

	/**
	 * AJAX: Generate support report.
	 *
	 * @return void
	 */
	public static function ajax_generate_support_report(): void {
		check_ajax_referer( 'wpshadow_troubleshoot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$user_id = get_current_user_id();
		$session = get_transient( self::SESSION_KEY . '_' . $user_id );

		if ( empty( $session ) ) {
			wp_send_json_error( array( 'message' => __( 'No active session found.', 'plugin-wpshadow' ) ) );
		}

		$report = self::generate_support_report( $session );

		wp_send_json_success(
			array(
				'report'   => $report,
				'filename' => 'wps-troubleshooting-report-' . date( 'Y-m-d-His' ) . '.txt',
			)
		);
	}

	/**
	 * Generate comprehensive support report.
	 *
	 * @param array $session Session data.
	 * @return string
	 */
	private static function generate_support_report( array $session ): string {
		$issue    = $session['issue'] ?? 'unknown';
		$category = self::$issue_categories[ $issue ] ?? array();
		$findings = $session['findings'] ?? array();

		$report  = "=== WPShadow Troubleshooting Report ===\n\n";
		$report .= 'Generated: ' . date( 'Y-m-d H:i:s' ) . "\n";
		$report .= 'Site URL: ' . site_url() . "\n\n";

		$report .= "=== Issue Information ===\n";
		$report .= 'Issue Type: ' . ( $category['title'] ?? 'Unknown' ) . "\n";
		$report .= 'Description: ' . ( $category['description'] ?? '' ) . "\n\n";

		$report .= "=== Diagnostic Findings ===\n";
		if ( ! empty( $findings ) ) {
			foreach ( $findings as $finding ) {
				$report .= sprintf(
					"[%s] %s\n",
					strtoupper( $finding['severity'] ),
					$finding['message']
				);
				if ( ! empty( $finding['details'] ) ) {
						$report .= '  Details: ' . $finding['details'] . "\n";
				}
			}
		} else {
			$report .= "No specific findings recorded.\n";
		}
		$report .= "\n";

		$report .= "=== System Information ===\n";
		$report .= 'WordPress Version: ' . get_bloginfo( 'version' ) . "\n";
		$report .= 'PHP Version: ' . phpversion() . "\n";
		$report .= 'MySQL Version: ' . $GLOBALS['wpdb']->db_version() . "\n";
		$report .= 'Web Server: ' . ( $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ) . "\n\n";

		$report .= "=== Active Theme ===\n";
		$theme   = wp_get_theme();
		$report .= 'Name: ' . $theme->get( 'Name' ) . "\n";
		$report .= 'Version: ' . $theme->get( 'Version' ) . "\n\n";

		$report .= "=== Active Plugins ===\n";
		$plugins = get_plugins();
		$active  = get_option( 'active_plugins', array() );
		foreach ( $active as $plugin_path ) {
			if ( isset( $plugins[ $plugin_path ] ) ) {
				$plugin  = $plugins[ $plugin_path ];
				$report .= sprintf(
					"%s v%s\n",
					$plugin['Name'],
					$plugin['Version'] ?? 'unknown'
				);
			}
		}

		$report .= "\n=== End of Report ===\n";

		return $report;
	}

	/**
	 * Add dashboard widget for quick access.
	 *
	 * @return void
	 */
	public static function add_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_meta_box(
			'wpshadow_troubleshooting_widget',
			__( 'Troubleshooting Wizard', 'plugin-wpshadow' ),
			array( __CLASS__, 'render_dashboard_widget' ),
			'dashboard',
			'side',
			'high'
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return void
	 */
	public static function render_dashboard_widget(): void {
		?>
<div class="wps-troubleshoot-widget">
<p><?php esc_html_e( 'Having WordPress issues? Let our wizard guide you through diagnosis and fixes.', 'plugin-wpshadow' ); ?></p>
<p>
<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-troubleshoot' ) ); ?>" class="button button-primary">
		<?php esc_html_e( 'Start Troubleshooting', 'plugin-wpshadow' ); ?>
</a>
</p>
</div>
		<?php
	}
}
