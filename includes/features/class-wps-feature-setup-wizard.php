<?php declare(strict_types=1);
/**
 * Site Setup & Configuration Health Check Feature
 *
 * Comprehensive setup wizard and ongoing health check system that ensures
 * WordPress sites are properly configured for security, SEO, and performance.
 *
 * @package    WPShadow
 * @subpackage Features
 * @since      1.0.0
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * Site Setup Wizard Feature Class
 *
 * Validates essential WordPress settings and guides users through proper configuration.
 *
 * @since 1.0.0
 */
final class WPSHADOW_Feature_Setup_Wizard extends WPSHADOW_Abstract_Feature {

	/**
	 * Configuration checks results cache.
	 *
	 * @var array
	 */
	private array $check_results = array();

	/**
	 * Feature constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'setup-wizard',
				'name'               => __( 'Site Babysitter', 'wpshadow' ),
				'description_short'  => __( 'Setup wizard and configuration health checks', 'wpshadow' ),
				'description_long'   => __( 'Your comprehensive WordPress babysitter! Guides you through proper site setup and continuously monitors critical configuration settings. Checks that search engines aren\'t accidentally blocked, users are properly configured, SEO settings are optimized, permalinks are working, and security essentials are in place. Like having an expert constantly reviewing your site\'s fundamental configuration to catch common mistakes before they become problems.', 'wpshadow' ),
				'description_wizard' => __( 'Get guided through proper WordPress setup and continuously monitor configuration health. Catches common mistakes like blocking search engines or using default usernames.', 'wpshadow' ),
				'aliases'            => array( 'setup', 'configuration', 'site setup', 'onboarding', 'initial setup', 'wordpress setup', 'configuration wizard', 'health check' ),
				'sub_features'       => array(
					'user_config'        => array(
						'name'               => __( 'User Configuration Check', 'wpshadow' ),
						'description_short'  => __( 'Verify admin user is properly configured', 'wpshadow' ),
						'description_long'   => __( 'Checks that admin user has proper display name (not "admin" or username), email is valid, and profile is reasonably complete. Warns if using default "admin" username (major security risk) or if admin email hasn\'t been verified. Proper user configuration prevents security issues and ensures site credibility.', 'wpshadow' ),
						'description_wizard' => __( 'Make sure your admin account is properly set up with a secure username, verified email, and appropriate display name.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'site_identity'      => array(
						'name'               => __( 'Site Identity Check', 'wpshadow' ),
						'description_short'  => __( 'Verify site title and basic info are customized', 'wpshadow' ),
						'description_long'   => __( 'Validates that site title, tagline, language, and timezone are properly configured (not default values). Detects timezone via browser for accuracy. Ensures site identity is complete and correct. Mismatched timezone affects scheduled posts and email timestamps.', 'wpshadow' ),
						'description_wizard' => __( 'Ensure your site has a proper title, tagline, language, and correct timezone settings.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'seo_visibility'     => array(
						'name'               => __( 'SEO & Visibility Check', 'wpshadow' ),
						'description_short'  => __( 'Check if search engines can see your site', 'wpshadow' ),
						'description_long'   => __( 'CRITICAL CHECK: Verifies that search engine blocking is not enabled (Settings > Reading "Discourage search engines"). This is the most common WordPress configuration mistake - sites accidentally hiding themselves from Google. Checks robots.txt accessibility and sitemap configuration. A single checkbox can disable all SEO - this warns you if it\'s been accidentally enabled.', 'wpshadow' ),
						'description_wizard' => __( 'Make sure you\'re not accidentally hiding your site from Google and other search engines - this single mistake can destroy your SEO.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'permalinks'         => array(
						'name'               => __( 'Permalink Structure Check', 'wpshadow' ),
						'description_short'  => __( 'Verify permalink structure is SEO-friendly', 'wpshadow' ),
						'description_long'   => __( 'Tests if permalink structure is set to something SEO-friendly (like post name) rather than default ?p=123 format. Verifies permalink rewrite rules are working correctly by testing an actual URL rewrite. Checks if .htaccess is writable on Apache servers. Recommends optimal permalink structure for Google ranking.', 'wpshadow' ),
						'description_wizard' => __( 'Set up clean, SEO-friendly URLs so Google can easily understand your page structure.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'essential_settings' => array(
						'name'               => __( 'Essential Settings Check', 'wpshadow' ),
						'description_short'  => __( 'Verify core WordPress settings are correct', 'wpshadow' ),
						'description_long'   => __( 'Checks that site URL matches WordPress URL, homepage display is configured, posts per page is reasonable, comments have spam protection, media uploads are restricted appropriately. Verifies that critical settings like default post category aren\'t left as "Uncategorized". These settings form the foundation of proper WordPress configuration.', 'wpshadow' ),
						'description_wizard' => __( 'Ensure all core WordPress settings like URLs, homepage display, and comment settings are properly configured.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'security_basics'    => array(
						'name'               => __( 'Security Basics Check', 'wpshadow' ),
						'description_short'  => __( 'Verify user registration and role settings', 'wpshadow' ),
						'description_long'   => __( 'Checks if user registration is enabled when it shouldn\'t be, validates default user role is set appropriately, warns if open registration allows anyone to join with dangerous roles. Verifies file upload restrictions are in place. These basics prevent unauthorized access and reduce attack surface.', 'wpshadow' ),
						'description_wizard' => __( 'Make sure user registration is configured correctly and people can\'t create admin accounts on your site.', 'wpshadow' ),
						'default_enabled'    => true,
					),
				),
			)
		);
	}

	/**
	 * Check if feature has details page.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register feature hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		// Schedule periodic checks
		add_action( 'wp', array( $this, 'schedule_health_checks' ) );
		add_action( 'wpshadow_setup_wizard_check', array( $this, 'run_all_checks' ) );

		// Admin initialization
		add_action( 'admin_init', array( $this, 'check_critical_issues' ) );
		add_action( 'admin_notices', array( $this, 'display_setup_notices' ) );

		// AJAX handlers
		add_action( 'wp_ajax_wpshadow_run_setup_checks', array( $this, 'ajax_run_all_checks' ) );
		add_action( 'wp_ajax_wpshadow_apply_configuration_fix', array( $this, 'ajax_apply_fix' ) );
		add_action( 'wp_ajax_wpshadow_set_timezone', array( $this, 'ajax_set_timezone' ) );
	}

	/**
	 * Schedule periodic health checks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function schedule_health_checks(): void {
		if ( ! wp_next_scheduled( 'wpshadow_setup_wizard_check' ) ) {
			wp_schedule_event( time(), 'weekly', 'wpshadow_setup_wizard_check' );
		}
	}

	/**
	 * Run all configuration checks.
	 *
	 * @since 1.0.0
	 * @return array Configuration check results.
	 */
	public function run_all_checks(): array {
		$results = array();

		if ( $this->is_sub_feature_enabled( 'user_config' ) ) {
			$results['user_config'] = $this->check_user_configuration();
		}
		if ( $this->is_sub_feature_enabled( 'site_identity' ) ) {
			$results['site_identity'] = $this->check_site_identity();
		}
		if ( $this->is_sub_feature_enabled( 'seo_visibility' ) ) {
			$results['seo_visibility'] = $this->check_seo_visibility();
		}
		if ( $this->is_sub_feature_enabled( 'permalinks' ) ) {
			$results['permalinks'] = $this->check_permalink_structure();
		}
		if ( $this->is_sub_feature_enabled( 'essential_settings' ) ) {
			$results['essential_settings'] = $this->check_essential_settings();
		}
		if ( $this->is_sub_feature_enabled( 'security_basics' ) ) {
			$results['security_basics'] = $this->check_security_basics();
		}

		$this->check_results = $results;
		set_transient( 'wpshadow_setup_wizard_results', $results, DAY_IN_SECONDS );
		$this->log_configuration_issues( $results );

		return $results;
	}

	/**
	 * Check user configuration.
	 *
	 * @since 1.0.0
	 * @return array Check results.
	 */
	private function check_user_configuration(): array {
		$admin_user = get_user_by( 'login', 'admin' );
		$issues     = array();

		// Check for default 'admin' username
		if ( $admin_user ) {
			$issues[] = array(
				'severity' => 'critical',
				'message'  => __( 'Default "admin" username detected - major security risk. Rename this account immediately.', 'wpshadow' ),
				'fixable'  => false,
			);
		}

		// Check admin display name
		$current_user = wp_get_current_user();
		if ( $current_user && $current_user->ID ) {
			$display_name = $current_user->display_name;
			if ( empty( $display_name ) || $display_name === $current_user->user_login || $display_name === 'admin' ) {
				$issues[] = array(
					'severity' => 'warning',
					'message'  => __( 'Admin account has no custom display name. Set a professional display name in your profile.', 'wpshadow' ),
					'fixable'  => false,
				);
			}
		}

		// Check admin email
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || false === is_email( $admin_email ) ) {
			$issues[] = array(
				'severity' => 'critical',
				'message'  => __( 'Admin email is invalid. Update it in Settings > General.', 'wpshadow' ),
				'fixable'  => true,
				'fix_link' => admin_url( 'options-general.php' ),
				'fix_text' => __( 'Update Admin Email', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'status'   => 'warning',
				'message'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d user configuration issue(s) detected.', 'wpshadow' ),
					count( $issues )
				),
				'issues'   => $issues,
				'severity' => 'medium',
			);
		}

		return array(
			'status'  => 'pass',
			'message' => __( 'User configuration is properly set up.', 'wpshadow' ),
		);
	}

	/**
	 * Check site identity settings.
	 *
	 * @since 1.0.0
	 * @return array Check results.
	 */
	private function check_site_identity(): array {
		$issues = array();

		// Check site title
		$site_title = get_option( 'blogname' );
		if ( empty( $site_title ) || 'My Site' === $site_title || 'WordPress' === $site_title ) {
			$issues[] = array(
				'severity' => 'warning',
				'message'  => __( 'Site title is not customized. Set a descriptive site title in Settings > General.', 'wpshadow' ),
				'fixable'  => true,
				'fix_link' => admin_url( 'options-general.php' ),
				'fix_text' => __( 'Customize Site Title', 'wpshadow' ),
			);
		}

		// Check tagline
		$tagline = get_option( 'blogdescription' );
		if ( empty( $tagline ) || 'Just another WordPress site' === $tagline ) {
			$issues[] = array(
				'severity' => 'info',
				'message'  => __( 'Site tagline is default or empty. Add a descriptive tagline that explains what your site is about.', 'wpshadow' ),
				'fixable'  => true,
				'fix_link' => admin_url( 'options-general.php' ),
				'fix_text' => __( 'Add Site Tagline', 'wpshadow' ),
			);
		}

		// Check site language
		$site_language = get_option( 'lang' );
		if ( empty( $site_language ) || 'en_US' === $site_language ) {
			// This is usually fine, only warn if it seems wrong
		}

		// Check timezone
		$timezone = get_option( 'timezone_string' );
		if ( empty( $timezone ) || 'UTC' === $timezone ) {
			$issues[] = array(
				'severity' => 'info',
				'message'  => __( 'Timezone is not explicitly set or is UTC. Set to your actual timezone for accurate scheduling.', 'wpshadow' ),
				'fixable'  => true,
				'fix_link' => admin_url( 'options-general.php' ),
				'fix_text' => __( 'Set Timezone', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'status'   => 'warning',
				'message'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d site identity issue(s) found.', 'wpshadow' ),
					count( $issues )
				),
				'issues'   => $issues,
				'severity' => 'low',
			);
		}

		return array(
			'status'  => 'pass',
			'message' => __( 'Site identity is properly configured.', 'wpshadow' ),
		);
	}

	/**
	 * Check SEO and visibility settings.
	 *
	 * @since 1.0.0
	 * @return array Check results.
	 */
	private function check_seo_visibility(): array {
		$issues = array();

		// CRITICAL: Check if search engines are blocked
		$discourage_search_engines = get_option( 'blog_public' );
		if ( ! $discourage_search_engines ) {
			$issues[] = array(
				'severity' => 'critical',
				'message'  => __( '🚨 CRITICAL: Search engines are blocked from indexing your site! This is the single most damaging WordPress mistake. Disable "Discourage search engines" in Settings > Reading immediately.', 'wpshadow' ),
				'fixable'  => true,
				'fix_link' => admin_url( 'options-reading.php' ),
				'fix_text' => __( 'Fix Search Engine Blocking', 'wpshadow' ),
			);
		}

		// Check if robots.txt is accessible
		$robots_url = home_url( '/robots.txt' );
		$response   = wp_remote_head( $robots_url, array( 'timeout' => 5 ) );
		if ( is_wp_error( $response ) || 404 === wp_remote_retrieve_response_code( $response ) ) {
			$issues[] = array(
				'severity' => 'info',
				'message'  => __( 'robots.txt not found. WordPress usually generates this automatically, but it\'s not accessible.', 'wpshadow' ),
				'fixable'  => false,
			);
		}

		// Check sitemap (WordPress 5.5+)
		if ( function_exists( 'wp_sitemaps_get_sitemaps' ) ) {
			$sitemaps = wp_sitemaps_get_sitemaps();
			if ( empty( $sitemaps ) ) {
				$issues[] = array(
					'severity' => 'info',
					'message'  => __( 'XML sitemap is not generating. This is needed for Google to understand your site structure.', 'wpshadow' ),
					'fixable'  => false,
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'status'   => 'critical' === $issues[0]['severity'] ? 'critical' : 'warning',
				'message'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d SEO/visibility issue(s) detected.', 'wpshadow' ),
					count( $issues )
				),
				'issues'   => $issues,
				'severity' => 'critical' === $issues[0]['severity'] ? 'high' : 'medium',
			);
		}

		return array(
			'status'  => 'pass',
			'message' => __( 'SEO and visibility settings are properly configured.', 'wpshadow' ),
		);
	}

	/**
	 * Check permalink structure.
	 *
	 * @since 1.0.0
	 * @return array Check results.
	 */
	private function check_permalink_structure(): array {
		$issues = array();

		// Check if using default structure
		$permalink_structure = get_option( 'permalink_structure' );
		if ( empty( $permalink_structure ) || '/%year%/%monthnum%/%day%/%postname%/' === $permalink_structure ) {
			$issues[] = array(
				'severity' => 'warning',
				'message'  => __( 'Permalink structure is not optimized for SEO. Recommend using /%postname%/ structure for cleaner, more SEO-friendly URLs.', 'wpshadow' ),
				'fixable'  => true,
				'fix_link' => admin_url( 'options-permalink.php' ),
				'fix_text' => __( 'Set Optimal Permalinks', 'wpshadow' ),
			);
		}

		// Test if rewrite rules are working
		$test_post = get_posts( array( 'posts_per_page' => 1 ) );
		if ( ! empty( $test_post ) ) {
			$test_url = get_permalink( $test_post[0]->ID );
			$response = wp_remote_get( $test_url, array( 'timeout' => 5 ) );
			if ( is_wp_error( $response ) || 404 === wp_remote_retrieve_response_code( $response ) ) {
				$issues[] = array(
					'severity' => 'critical',
					'message'  => __( 'Permalink rewrite rules are not working. Check .htaccess is writable and mod_rewrite is enabled on Apache.', 'wpshadow' ),
					'fixable'  => true,
					'fix_link' => admin_url( 'options-permalink.php' ),
					'fix_text' => __( 'Reset Permalink Rules', 'wpshadow' ),
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'status'   => 'critical' === $issues[0]['severity'] ? 'critical' : 'warning',
				'message'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d permalink issue(s) found.', 'wpshadow' ),
					count( $issues )
				),
				'issues'   => $issues,
				'severity' => 'critical' === $issues[0]['severity'] ? 'high' : 'medium',
			);
		}

		return array(
			'status'  => 'pass',
			'message' => __( 'Permalink structure is properly configured.', 'wpshadow' ),
		);
	}

	/**
	 * Check essential WordPress settings.
	 *
	 * @since 1.0.0
	 * @return array Check results.
	 */
	private function check_essential_settings(): array {
		$issues = array();

		// Check if site URL matches WordPress URL
		$home_url = home_url();
		$site_url = site_url();
		if ( $home_url !== $site_url ) {
			$issues[] = array(
				'severity' => 'warning',
				'message'  => __( 'Site URL and WordPress URL do not match. This can cause issues with site functionality.', 'wpshadow' ),
				'fixable'  => false,
			);
		}

		// Check homepage display setting
		$homepage_setting = get_option( 'show_on_front' );
		if ( 'posts' !== $homepage_setting && 'page' !== $homepage_setting ) {
			$issues[] = array(
				'severity' => 'info',
				'message'  => __( 'Homepage display setting is unusual. Set to either "Your latest posts" or "A static page" in Settings > Reading.', 'wpshadow' ),
				'fixable'  => true,
				'fix_link' => admin_url( 'options-reading.php' ),
				'fix_text' => __( 'Configure Homepage', 'wpshadow' ),
			);
		}

		// Check posts per page
		$posts_per_page = (int) get_option( 'posts_per_page' );
		if ( $posts_per_page > 20 ) {
			$issues[] = array(
				'severity' => 'info',
				'message'  => sprintf(
					/* translators: %d: number of posts */
					__( 'Posts per page is set to %d, which may slow down archive pages. Recommend 10-15 posts per page.', 'wpshadow' ),
					$posts_per_page
				),
				'fixable'  => true,
				'fix_link' => admin_url( 'options-reading.php' ),
				'fix_text' => __( 'Optimize Posts Per Page', 'wpshadow' ),
			);
		}

		// Check comment settings
		$comments_enabled = get_option( 'default_comment_status' );
		if ( 'open' === $comments_enabled ) {
			// Check if spam protection is enabled
			if ( ! class_exists( 'Akismet' ) && ! get_option( 'moderate_comments_notify' ) ) {
				$issues[] = array(
					'severity' => 'info',
					'message'  => __( 'Comments are enabled but spam protection (Akismet) is not active. Consider enabling comment moderation or using Akismet.', 'wpshadow' ),
					'fixable'  => false,
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'status'   => 'warning',
				'message'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d setting(s) could be optimized.', 'wpshadow' ),
					count( $issues )
				),
				'issues'   => $issues,
				'severity' => 'low',
			);
		}

		return array(
			'status'  => 'pass',
			'message' => __( 'Essential settings are properly configured.', 'wpshadow' ),
		);
	}

	/**
	 * Check security basics.
	 *
	 * @since 1.0.0
	 * @return array Check results.
	 */
	private function check_security_basics(): array {
		$issues = array();

		// Check user registration
		$user_registration = get_option( 'users_can_register' );
		if ( $user_registration ) {
			$default_role = get_option( 'default_user_role' );
			if ( 'administrator' === $default_role || 'editor' === $default_role ) {
				$issues[] = array(
					'severity' => 'critical',
					'message'  => sprintf(
						/* translators: %s: user role */
						__( 'User registration is open and default role is "%s" - anyone can create accounts with that permission level!', 'wpshadow' ),
						ucfirst( $default_role )
					),
					'fixable'  => true,
					'fix_link' => admin_url( 'options-general.php' ),
					'fix_text' => __( 'Fix Registration Settings', 'wpshadow' ),
				);
			} else {
				$issues[] = array(
					'severity' => 'info',
					'message'  => sprintf(
						/* translators: %s: user role */
						__( 'User registration is enabled with "%s" role. Make sure this is intentional - most sites don\'t need public registration.', 'wpshadow' ),
						ucfirst( $default_role )
					),
					'fixable'  => true,
					'fix_link' => admin_url( 'options-general.php' ),
					'fix_text' => __( 'Review Registration Settings', 'wpshadow' ),
				);
			}
		}

		// Check file upload settings
		$upload_filetypes = get_option( 'upload_filetypes' );
		if ( empty( $upload_filetypes ) ) {
			// WordPress restricts by default, which is good
		}

		if ( ! empty( $issues ) ) {
			return array(
				'status'   => 'critical' === $issues[0]['severity'] ? 'critical' : 'warning',
				'message'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d security issue(s) detected.', 'wpshadow' ),
					count( $issues )
				),
				'issues'   => $issues,
				'severity' => 'critical' === $issues[0]['severity'] ? 'high' : 'medium',
			);
		}

		return array(
			'status'  => 'pass',
			'message' => __( 'Security basics are properly configured.', 'wpshadow' ),
		);
	}

	/**
	 * Check for critical issues and log them.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function check_critical_issues(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'dashboard' !== $screen->id ) {
			return;
		}

		$results = get_transient( 'wpshadow_setup_wizard_results' );
		if ( false === $results ) {
			$results = $this->run_all_checks();
		}

		$this->check_results = $results;
	}

	/**
	 * Display setup wizard notices in admin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function display_setup_notices(): void {
		if ( ! $this->get_setting( 'show_admin_notices', true, false ) ) {
			return;
		}

		if ( empty( $this->check_results ) ) {
			return;
		}

		// Find critical issues
		$critical_issues = array();
		foreach ( $this->check_results as $check => $result ) {
			if ( 'critical' === $result['status'] && ! empty( $result['issues'] ) ) {
				foreach ( $result['issues'] as $issue ) {
					if ( 'critical' === $issue['severity'] ) {
						$critical_issues[] = $issue;
					}
				}
			}
		}

		if ( ! empty( $critical_issues ) ) {
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p><strong>' . esc_html__( 'WPShadow Site Babysitter: Critical Configuration Issues', 'wpshadow' ) . '</strong></p>';
			echo '<ul style="list-style: disc; margin-left: 20px;">';
			foreach ( $critical_issues as $issue ) {
				echo '<li>' . esc_html( $issue['message'] );
				if ( ! empty( $issue['fix_link'] ) ) {
					echo ' <a href="' . esc_url( $issue['fix_link'] ) . '">' . esc_html( $issue['fix_text'] ) . '</a>';
				}
				echo '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}
	}

	/**
	 * Log configuration issues found.
	 *
	 * @since 1.0.0
	 * @param array $results Check results.
	 * @return void
	 */
	private function log_configuration_issues( array $results ): void {
		$log_all = $this->get_setting( 'log_all_checks', false, false );

		foreach ( $results as $check => $result ) {
			if ( 'critical' === $result['status'] || 'warning' === $result['status'] ) {
				$this->log_activity(
					ucfirst( str_replace( '_', ' ', $check ) ),
					$result['message'],
					'critical' === $result['status'] ? 'error' : 'warning'
				);
			} elseif ( $log_all && 'pass' === $result['status'] ) {
				$this->log_activity(
					ucfirst( str_replace( '_', ' ', $check ) ),
					$result['message'],
					'info'
				);
			}
		}
	}

	/**
	 * AJAX handler to run all checks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_run_all_checks(): void {
		check_ajax_referer( 'wpshadow_diagnostics', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$results = $this->run_all_checks();
		wp_send_json_success(
			array(
				'results' => $results,
				'message' => __( 'Configuration checks completed.', 'wpshadow' ),
			)
		);
	}

	/**
	 * AJAX handler to apply configuration fix.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_apply_fix(): void {
		check_ajax_referer( 'wpshadow_diagnostics', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$fix_type = isset( $_POST['fix_type'] ) ? sanitize_text_field( wp_unslash( $_POST['fix_type'] ) ) : '';

		$result = false;
		$message = '';

		switch ( $fix_type ) {
			case 'enable_search_engines':
				update_option( 'blog_public', 1 );
				$result  = true;
				$message = __( 'Search engines are now allowed to index your site.', 'wpshadow' );
				$this->log_activity( 'Fixed SEO Visibility', 'Enabled search engine indexing.', 'success' );
				break;

			case 'optimize_permalinks':
				update_option( 'permalink_structure', '/%postname%/' );
				flush_rewrite_rules();
				$result  = true;
				$message = __( 'Permalink structure optimized to /%postname%/.', 'wpshadow' );
				$this->log_activity( 'Fixed Permalink Structure', 'Set optimal permalink structure.', 'success' );
				break;

			case 'reset_permalink_rules':
				flush_rewrite_rules();
				$result  = true;
				$message = __( 'Permalink rewrite rules have been reset.', 'wpshadow' );
				$this->log_activity( 'Reset Permalink Rules', 'Flushed rewrite rules to fix 404 errors.', 'success' );
				break;

			case 'disable_public_registration':
				update_option( 'users_can_register', 0 );
				$result  = true;
				$message = __( 'Public user registration has been disabled.', 'wpshadow' );
				$this->log_activity( 'Fixed Security', 'Disabled public user registration.', 'success' );
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Unknown fix type.', 'wpshadow' ) ) );
		}

		if ( $result ) {
			wp_send_json_success( array( 'message' => $message ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to apply fix.', 'wpshadow' ) ) );
		}
	}

	/**
	 * AJAX handler to set timezone.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_set_timezone(): void {
		check_ajax_referer( 'wpshadow_diagnostics', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$timezone = isset( $_POST['timezone'] ) ? sanitize_text_field( wp_unslash( $_POST['timezone'] ) ) : '';

		// Validate timezone
		if ( empty( $timezone ) || ! in_array( $timezone, timezone_identifiers_list(), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid timezone.', 'wpshadow' ) ) );
		}

		update_option( 'timezone_string', $timezone );
		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %s: timezone */
					__( 'Timezone set to %s.', 'wpshadow' ),
					$timezone
				),
			)
		);
	}

	/**
	 * Cleanup on disable.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function on_disable(): void {
		$timestamp = wp_next_scheduled( 'wpshadow_setup_wizard_check' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'wpshadow_setup_wizard_check' );
		}
		delete_transient( 'wpshadow_setup_wizard_results' );
	}
}
