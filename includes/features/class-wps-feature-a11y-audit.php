<?php declare(strict_types=1);
/**
 * Feature: Accessibility Audit
 *
 * Detects accessibility violations (WCAG) and offers auto-fixes.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_A11y_Audit extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'a11y-audit',
			'name'        => __( 'Accessibility Checker', 'wpshadow' ),
			'description' => __( 'Find and fix problems that make your site hard to use for people with disabilities.', 'wpshadow' ),
			'aliases'     => array( 'accessibility audit', 'wcag', 'a11y', 'accessibility check', 'alt text', 'aria', 'keyboard navigation', 'screen reader', 'disability access', 'ada compliance', 'accessibility validation', 'inclusive design' ),
			'sub_features' => array(
				'alt_text_check'     => __( 'Check for missing image descriptions', 'wpshadow' ),
				'aria_validation'    => __( 'Check screen reader labels', 'wpshadow' ),
				'keyboard_navigation' => __( 'Check keyboard navigation works', 'wpshadow' ),
				'contrast_checking'  => __( 'Check text colors are readable', 'wpshadow' ),
				'auto_fixes'         => __( 'Fix problems automatically', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'alt_text_check'     => true,
			'aria_validation'    => true,
			'keyboard_navigation' => true,
			'contrast_checking'  => false,
			'auto_fixes'         => false,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Add focus indicators
		if ( $this->is_sub_feature_enabled( 'keyboard_navigation', true ) ) {
			add_action( 'wp_head', array( $this, 'add_focus_indicators' ), 999 );
		}

		// Off-hours cron scheduling (2 AM daily)
		add_action( 'wp', array( $this, 'schedule_off_hours_audit' ) );
		add_action( 'wpshadow_a11y_audit_cron', array( $this, 'run_scheduled_audit' ) );

		// Pre-publish review
		add_action( 'pre_post_update', array( $this, 'audit_before_publish' ), 10, 1 );

		// On-demand page scan
		add_action( 'wp_ajax_wpshadow_audit_page', array( $this, 'ajax_audit_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_audit_admin_assets' ) );

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Schedule off-hours audit if not already scheduled.
	 */
	public function schedule_off_hours_audit(): void {
		if ( ! wp_next_scheduled( 'wpshadow_a11y_audit_cron' ) ) {
			// Schedule for 2 AM daily
			wp_schedule_event( strtotime( 'tomorrow 2:00 AM' ), 'daily', 'wpshadow_a11y_audit_cron' );
		}
	}

	/**
	 * Run scheduled audit (off-hours cron).
	 */
	public function run_scheduled_audit(): void {
		$issues = $this->audit_content();
		set_transient( 'wpshadow_a11y_audit_cache', $issues, WEEK_IN_SECONDS );
		$this->log_activity( 'a11y_audit_scheduled', sprintf( 'Off-hours audit complete: %d issues found', count( $issues ) ), 'info' );
		do_action( 'wpshadow_a11y_audit_complete', $issues );
	}

	/**
	 * Audit content before publishing (pre-publish review).
	 */
	public function audit_before_publish( int $post_id ): void {
		$post = get_post( $post_id );
		if ( ! $post || ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$issues = $this->audit_single_post( $post_id );

		if ( ! empty( $issues ) ) {
			$transient_key = 'wpshadow_a11y_review_' . $post_id;
			set_transient( $transient_key, $issues, HOUR_IN_SECONDS );
			do_action( 'wpshadow_a11y_pre_publish_issues', $post_id, $issues );
		}
	}

	/**
	 * AJAX handler: Audit a specific page.
	 */
	public function ajax_audit_page(): void {
		if ( ! check_ajax_referer( 'wpshadow_a11y_audit_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'wpshadow' ) ) );
			return;
		}

		$page_id = isset( $_POST['page_id'] ) ? (int) $_POST['page_id'] : 0;
		if ( $page_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid page ID', 'wpshadow' ) ) );
			return;
		}

		$issues = $this->audit_single_post( $page_id );
		$post = get_post( $page_id );

		wp_send_json_success( array(
			'post_id'      => $page_id,
			'post_title'   => $post->post_title,
			'issues_count' => count( $issues ),
			'issues'       => array_slice( $issues, 0, 20 ),
		) );
	}

	/**
	 * Audit a single post (on-demand).
	 */
	private function audit_single_post( int $post_id ): array {
		$post = get_post( $post_id );
		if ( ! $post || ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return array();
		}

		$issues = array();
		$content = $post->post_content;

		if ( ! $content ) {
			return $issues;
		}

		if ( $this->is_sub_feature_enabled( 'alt_text_check', true ) ) {
			if ( preg_match_all( '/<img\s+(?![^>]*\balt=)/i', $content ) ) {
				$issues[] = array(
					'type'    => 'missing_alt',
					'post_id' => $post_id,
					'message' => __( 'Image(s) missing alt text', 'wpshadow' ),
				);
			}
		}

		if ( $this->is_sub_feature_enabled( 'aria_validation', true ) ) {
			if ( preg_match( '/role=["\']([^"\']*)["\']/x', $content, $matches ) ) {
				if ( ! $this->is_valid_aria_role( $matches[1] ) ) {
					$issues[] = array(
						'type'    => 'invalid_aria',
						'post_id' => $post_id,
						'message' => sprintf( __( 'Invalid ARIA role: %s', 'wpshadow' ), $matches[1] ),
					);
				}
			}
		}

		if ( $this->is_sub_feature_enabled( 'keyboard_navigation', true ) ) {
			if ( preg_match( '/tabindex=["\']?([1-9]+)["\']?/', $content ) ) {
				$issues[] = array(
					'type'    => 'positive_tabindex',
					'post_id' => $post_id,
					'message' => __( 'Positive tabindex detected (breaks keyboard nav)', 'wpshadow' ),
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for accessibility issues in posts (batch audit for cron).
	 */
	private function audit_content(): array {
		$issues = array();

		// Check for images without alt text
		$args = array(
			'post_type'  => array( 'post', 'page' ),
			'numberposts' => -1,
		);

		$posts = get_posts( $args );

		foreach ( $posts as $post ) {
			if ( ! $post->post_content ) {
				continue;
			}

			// Find images without alt text
			if ( preg_match_all( '/<img\s+(?![^>]*\balt=)/i', $post->post_content ) ) {
				$issues[] = array(
					'type'    => 'missing_alt',
					'post_id' => $post->ID,
					'message' => __( 'Image(s) missing alt text', 'wpshadow' ),
				);
			}

			// Find invalid ARIA roles
			if ( preg_match( '/role=["\']([^"\']*)["\']/', $post->post_content, $matches ) ) {
				if ( ! $this->is_valid_aria_role( $matches[1] ) ) {
					$issues[] = array(
						'type'    => 'invalid_aria',
						'post_id' => $post->ID,
						'message' => sprintf( __( 'Invalid ARIA role: %s', 'wpshadow' ), $matches[1] ),
					);
				}
			}

			// Find positive tabindex values
			if ( preg_match( '/tabindex=["\']?([1-9]+)["\']?/', $post->post_content ) ) {
				$issues[] = array(
					'type'    => 'positive_tabindex',
					'post_id' => $post->ID,
					'message' => __( 'Positive tabindex detected (disables natural tab order)', 'wpshadow' ),
				);
			}
		}

		return $issues;
	}

	/**
	 * Check if ARIA role is valid.
	 */
	private function is_valid_aria_role( string $role ): bool {
		$valid_roles = array(
			'alert', 'alertdialog', 'application', 'article', 'banner', 'button',
			'checkbox', 'columnheader', 'combobox', 'complementary', 'contentinfo',
			'definition', 'dialog', 'directory', 'document', 'feed', 'figure',
			'form', 'group', 'heading', 'img', 'link', 'list', 'listbox', 'listitem',
			'log', 'main', 'marquee', 'math', 'menu', 'menubar', 'menuitem',
			'menuitemcheckbox', 'menuitemradio', 'navigation', 'note', 'option',
			'presentation', 'progressbar', 'radio', 'radiogroup', 'region',
			'row', 'rowgroup', 'rowheader', 'scrollbar', 'search', 'searchbox',
			'separator', 'slider', 'spinbutton', 'status', 'switch', 'tab',
			'tablist', 'tabpanel', 'term', 'textbox', 'timer', 'toolbar',
			'tooltip', 'tree', 'treegrid', 'treeitem',
		);

		return in_array( strtolower( $role ), $valid_roles, true );
	}

	/**
	 * Add focus indicators via CSS.
	 */
	public function add_focus_indicators(): void {
		?>
		<style id="wps-a11y-focus">
		a:focus, button:focus, input:focus, select:focus, textarea:focus, [tabindex]:focus {
			outline: 3px solid #4A90E2 !important;
			outline-offset: 2px !important;
		}
		a:focus:not(:focus-visible), button:focus:not(:focus-visible),
		input:focus:not(:focus-visible), select:focus:not(:focus-visible),
		textarea:focus:not(:focus-visible), [tabindex]:focus:not(:focus-visible) {
			outline: none !important;
		}
		</style>
		<?php
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['a11y_audit'] = array(
			'label'  => __( 'Accessibility Audit', 'wpshadow' ),
			'test'   => array( $this, 'test_a11y' ),
		);

		return $tests;
	}

	public function test_a11y(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Accessibility Audit', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable accessibility auditing for WCAG compliance.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'a11y_audit',
			);
		}

		$issues = get_transient( 'wpshadow_a11y_audit_cache' );
		if ( false === $issues ) {
			$issues = $this->audit_content();
			set_transient( 'wpshadow_a11y_audit_cache', $issues, HOUR_IN_SECONDS );
		}

		$enabled_count = 0;
		$subs = array( 'alt_text_check', 'aria_validation', 'keyboard_navigation', 'contrast_checking', 'auto_fixes' );
		foreach ( $subs as $sub ) {
			if ( $this->is_sub_feature_enabled( $sub, false ) ) {
				$enabled_count++;
			}
		}

		$status = empty( $issues ) ? 'good' : 'recommended';

		return array(
			'label'       => __( 'Accessibility Audit', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d issues detected. %d sub-features enabled. Runs daily at 2 AM + on pre-publish. Scan specific pages on-demand.', 'wpshadow' ),
				count( $issues ),
				$enabled_count
			),
			'actions'     => '',
			'test'        => 'a11y_audit',
		);
	}

	/**
	 * Enqueue audit assets in admin (feature page).
	 */
	public function enqueue_audit_admin_assets(): void {
		// Only load on feature admin page
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'wpshadow_page_wpshadow_features' !== $screen->id ) {
			return;
		}

		// CSS
		wp_enqueue_style(
			'wpshadow-a11y-audit',
			WPSHADOW_URL . 'assets/css/a11y-audit.css',
			array(),
			WPSHADOW_VERSION
		);

		// JS with nonce
		wp_enqueue_script(
			'wpshadow-a11y-audit',
			WPSHADOW_URL . 'assets/js/a11y-audit.js',
			array(),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-a11y-audit',
			'wpshadowA11y',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wpshadow_a11y_audit_nonce' ),
			)
		);
	}
}
