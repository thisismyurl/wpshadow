<?php
/**
 * Feature: Accessibility Audit
 *
 * Scans common accessibility issues: contrast, focus order, ARIA roles,
 * alt text completeness, keyboard traps; emits fix suggestions or auto-patches when safe.
 *
 * @package WPS\CoreSupport\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPS_Feature_A11y_Audit
 *
 * Provides comprehensive accessibility auditing and automated fixes.
 */
final class WPS_Feature_A11y_Audit extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'a11y-audit',
				'name'               => __( 'Accessibility Audit', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Check that people with disabilities can use your site fully and comfortably', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'accessibility',
				'widget_label'       => __( 'UX & Accessibility', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Improve user experience and accessibility standards', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Add admin menu for accessibility audit dashboard.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Register settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Auto-fix filters when enabled in options.
		$options = $this->get_options();

		if ( $options['auto_fix_images'] ?? false ) {
			add_filter( 'the_content', array( $this, 'auto_fix_images_in_content' ), 20 );
		}

		if ( $options['auto_fix_contrast'] ?? false ) {
			add_action( 'wp_head', array( $this, 'add_contrast_fixes' ) );
		}

		if ( $options['auto_fix_focus'] ?? true ) {
			add_action( 'wp_footer', array( $this, 'add_focus_indicators' ) );
		}

		if ( $options['auto_fix_aria'] ?? false ) {
			add_filter( 'the_content', array( $this, 'auto_fix_aria_in_content' ), 20 );
		}

		// AJAX handlers for manual audit.
		add_action( 'wp_ajax_wps_run_a11y_audit', array( $this, 'ajax_run_audit' ) );
		add_action( 'wp_ajax_wps_apply_a11y_fix', array( $this, 'ajax_apply_fix' ) );
	}

	/**
	 * Add admin menu for accessibility audit.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Accessibility Audit', 'plugin-wp-support-thisismyurl' ),
			__( 'A11y Audit', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wp-support-a11y-audit',
			array( $this, 'render_audit_page' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'wps_a11y_audit_options_group',
			'wps_a11y_audit_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => array(
					'auto_fix_images'   => false,
					'auto_fix_contrast' => false,
					'auto_fix_focus'    => true,
					'auto_fix_aria'     => false,
				),
			)
		);
	}

	/**
	 * Sanitize plugin options.
	 *
	 * @param array<string, mixed> $input Input options.
	 * @return array<string, bool> Sanitized options.
	 */
	public function sanitize_options( array $input ): array {
		$sanitized = array();

		$sanitized['auto_fix_images']   = ! empty( $input['auto_fix_images'] );
		$sanitized['auto_fix_contrast'] = ! empty( $input['auto_fix_contrast'] );
		$sanitized['auto_fix_focus']    = ! empty( $input['auto_fix_focus'] );
		$sanitized['auto_fix_aria']     = ! empty( $input['auto_fix_aria'] );

		return $sanitized;
	}

	/**
	 * Render the accessibility audit page.
	 *
	 * @return void
	 */
	public function render_audit_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
		}

		wp_enqueue_style(
			'wps-a11y-audit',
			plugins_url( 'assets/css/a11y-audit.css', dirname( __DIR__, 2 ) ),
			array(),
			filemtime( dirname( __DIR__, 2 ) . '/assets/css/a11y-audit.css' )
		);

		wp_enqueue_script(
			'wps-a11y-audit',
			plugins_url( 'assets/js/a11y-audit.js', dirname( __DIR__, 2 ) ),
			array( 'jquery' ),
			filemtime( dirname( __DIR__, 2 ) . '/assets/js/a11y-audit.js' ),
			true
		);

		wp_localize_script(
			'wps-a11y-audit',
			'wpsA11yAudit',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wps_a11y_audit_nonce' ),
				'strings' => array(
					'enterUrl' => __( 'Please enter a URL to audit.', 'plugin-wp-support-thisismyurl' ),
				),
			)
		);

		include dirname( __DIR__ ) . '/views/a11y-audit-page.php';
	}

	/**
	 * AJAX handler: Run accessibility audit.
	 *
	 * @return void
	 */
	public function ajax_run_audit(): void {
		check_ajax_referer( 'wps_a11y_audit_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';

		if ( empty( $url ) ) {
			wp_send_json_error( array( 'message' => __( 'URL is required.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$issues = $this->scan_url( $url );

		wp_send_json_success( array( 'issues' => $issues ) );
	}

	/**
	 * AJAX handler: Apply a specific fix.
	 *
	 * @return void
	 */
	public function ajax_apply_fix(): void {
		check_ajax_referer( 'wps_a11y_audit_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$fix_type = isset( $_POST['fix_type'] ) ? sanitize_key( wp_unslash( $_POST['fix_type'] ) ) : '';
		$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( empty( $fix_type ) || empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid fix parameters.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$result = $this->apply_fix( $fix_type, $post_id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Fix applied successfully.', 'plugin-wp-support-thisismyurl' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to apply fix.', 'plugin-wp-support-thisismyurl' ) ) );
		}
	}

	/**
	 * Scan a URL for accessibility issues.
	 *
	 * @param string $url URL to scan.
	 * @return array<int, array<string, mixed>> List of issues found.
	 */
	private function scan_url( string $url ): array {
		$issues = array();

		// Get the post ID from URL if it's a post.
		$post_id = url_to_postid( $url );

		if ( $post_id > 0 ) {
			$post = get_post( $post_id );
			if ( $post ) {
				$content = $post->post_content;

				// Scan for missing alt text.
				$issues = array_merge( $issues, $this->check_alt_text( $content, $post_id ) );

				// Scan for ARIA issues.
				$issues = array_merge( $issues, $this->check_aria_roles( $content, $post_id ) );

				// Scan for keyboard traps.
				$issues = array_merge( $issues, $this->check_keyboard_traps( $content, $post_id ) );
			}
		}

		// Add general issues that apply to all pages.
		$issues = array_merge( $issues, $this->check_contrast_issues() );
		$issues = array_merge( $issues, $this->check_focus_order() );

		return $issues;
	}

	/**
	 * Check for missing or empty alt text in images.
	 *
	 * @param string $content Content to scan.
	 * @param int    $post_id Post ID.
	 * @return array<int, array<string, mixed>> List of issues.
	 */
	private function check_alt_text( string $content, int $post_id ): array {
		$issues = array();

		// Find all img tags.
		preg_match_all( '/<img[^>]+>/i', $content, $matches );

		foreach ( $matches[0] as $img_tag ) {
			// Check if alt attribute exists (including empty ones).
			if ( ! preg_match( '/\balt=/i', $img_tag ) ) {
				// No alt attribute at all.
				$issues[] = array(
					'type'       => 'alt_text',
					'severity'   => 'high',
					'message'    => __( 'Image missing alt attribute', 'plugin-wp-support-thisismyurl' ),
					'element'    => $img_tag,
					'post_id'    => $post_id,
					'auto_fix'   => true,
					'fix_action' => 'add_alt_attribute',
					'suggestion' => __( 'Add descriptive alt text to help screen readers understand the image content.', 'plugin-wp-support-thisismyurl' ),
				);
			} elseif ( preg_match( '/\balt=(["\']?)([^"\'>]*)\1/i', $img_tag, $alt_match ) ) {
				// Alt attribute exists, check if it's empty.
				if ( empty( trim( $alt_match[2] ) ) ) {
					// Check for decorative images - if they have role="presentation" or are truly decorative.
					if ( ! str_contains( $img_tag, 'role="presentation"' ) && ! str_contains( $img_tag, 'role="none"' ) ) {
						$issues[] = array(
							'type'       => 'alt_text',
							'severity'   => 'medium',
							'message'    => __( 'Image has empty alt text without decorative role', 'plugin-wp-support-thisismyurl' ),
							'element'    => $img_tag,
							'post_id'    => $post_id,
							'auto_fix'   => true,
							'fix_action' => 'add_descriptive_alt',
							'suggestion' => __( 'Either add descriptive alt text or mark as decorative with role="presentation".', 'plugin-wp-support-thisismyurl' ),
						);
					}
				}
			}
		}

		return $issues;
	}

	/**
	 * Check for ARIA role issues.
	 *
	 * @param string $content Content to scan.
	 * @param int    $post_id Post ID.
	 * @return array<int, array<string, mixed>> List of issues.
	 */
	private function check_aria_roles( string $content, int $post_id ): array {
		$issues = array();

		// Valid ARIA roles.
		$valid_roles = array(
			'alert',
			'alertdialog',
			'application',
			'article',
			'banner',
			'button',
			'checkbox',
			'columnheader',
			'combobox',
			'complementary',
			'contentinfo',
			'definition',
			'dialog',
			'directory',
			'document',
			'feed',
			'figure',
			'form',
			'grid',
			'gridcell',
			'group',
			'heading',
			'img',
			'link',
			'list',
			'listbox',
			'listitem',
			'log',
			'main',
			'marquee',
			'math',
			'menu',
			'menubar',
			'menuitem',
			'menuitemcheckbox',
			'menuitemradio',
			'navigation',
			'none',
			'note',
			'option',
			'presentation',
			'progressbar',
			'radio',
			'radiogroup',
			'region',
			'row',
			'rowgroup',
			'rowheader',
			'scrollbar',
			'search',
			'searchbox',
			'separator',
			'slider',
			'spinbutton',
			'status',
			'switch',
			'tab',
			'table',
			'tablist',
			'tabpanel',
			'term',
			'textbox',
			'timer',
			'toolbar',
			'tooltip',
			'tree',
			'treegrid',
			'treeitem',
		);

		// Find all elements with role attributes.
		preg_match_all( '/role=["\']([^"\']*)["\']/', $content, $matches );

		foreach ( $matches[1] as $role ) {
			$role = strtolower( trim( $role ) );
			if ( ! in_array( $role, $valid_roles, true ) ) {
				$issues[] = array(
					'type'       => 'aria_role',
					'severity'   => 'high',
					'message'    => sprintf(
						/* translators: %s: Invalid ARIA role name */
						__( 'Invalid ARIA role: %s', 'plugin-wp-support-thisismyurl' ),
						esc_html( $role )
					),
					'element'    => $role,
					'post_id'    => $post_id,
					'auto_fix'   => false,
					'suggestion' => __( 'Remove or replace with a valid ARIA role. See ARIA specification for valid roles.', 'plugin-wp-support-thisismyurl' ),
				);
			}
		}

		// Check for buttons without proper roles or labels.
		preg_match_all( '/<button[^>]*>([^<]*)<\/button>/i', $content, $button_matches );
		foreach ( $button_matches[0] as $idx => $button_tag ) {
			$button_text = trim( wp_strip_all_tags( $button_matches[1][ $idx ] ) );

			// Check if button has no text and no aria-label.
			if ( empty( $button_text ) && ! preg_match( '/aria-label=["\']([^"\']+)["\']/', $button_tag ) ) {
				$issues[] = array(
					'type'       => 'aria_role',
					'severity'   => 'high',
					'message'    => __( 'Button has no accessible label', 'plugin-wp-support-thisismyurl' ),
					'element'    => $button_tag,
					'post_id'    => $post_id,
					'auto_fix'   => false,
					'suggestion' => __( 'Add visible text content or aria-label attribute to the button.', 'plugin-wp-support-thisismyurl' ),
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for potential keyboard traps.
	 *
	 * @param string $content Content to scan.
	 * @param int    $post_id Post ID.
	 * @return array<int, array<string, mixed>> List of issues.
	 */
	private function check_keyboard_traps( string $content, int $post_id ): array {
		$issues = array();

		// Check for tabindex values greater than 0 (which can create keyboard traps).
		preg_match_all( '/tabindex=["\']([^"\']*)["\']/', $content, $matches );

		foreach ( $matches[1] as $tabindex ) {
			$index_value = intval( $tabindex );
			if ( $index_value > 0 ) {
				$issues[] = array(
					'type'       => 'keyboard_trap',
					'severity'   => 'medium',
					'message'    => sprintf(
						/* translators: %d: tabindex value */
						__( 'Positive tabindex value detected: %d', 'plugin-wp-support-thisismyurl' ),
						$index_value
					),
					'element'    => 'tabindex="' . esc_attr( $tabindex ) . '"',
					'post_id'    => $post_id,
					'auto_fix'   => true,
					'fix_action' => 'remove_tabindex',
					'suggestion' => __( 'Use tabindex="0" for programmatically focusable elements or tabindex="-1" to remove from tab order. Positive values disrupt natural tab order.', 'plugin-wp-support-thisismyurl' ),
				);
			}
		}

		// Check for elements that might trap focus (modals without proper close mechanisms).
		if ( preg_match_all( '/role=["\']dialog["\']/', $content, $dialog_matches ) ) {
			foreach ( $dialog_matches[0] as $dialog ) {
				$issues[] = array(
					'type'       => 'keyboard_trap',
					'severity'   => 'medium',
					'message'    => __( 'Dialog/modal detected - ensure keyboard trap management', 'plugin-wp-support-thisismyurl' ),
					'element'    => $dialog,
					'post_id'    => $post_id,
					'auto_fix'   => false,
					'suggestion' => __( 'Ensure the dialog can be closed with Escape key and focus is properly trapped and restored.', 'plugin-wp-support-thisismyurl' ),
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for contrast issues (general recommendations).
	 *
	 * @return array<int, array<string, mixed>> List of issues.
	 */
	private function check_contrast_issues(): array {
		$issues = array();

		// This is a simplified check - full contrast checking requires analyzing rendered CSS.
		$issues[] = array(
			'type'       => 'contrast',
			'severity'   => 'info',
			'message'    => __( 'Contrast ratio check required', 'plugin-wp-support-thisismyurl' ),
			'element'    => __( 'Site-wide', 'plugin-wp-support-thisismyurl' ),
			'post_id'    => 0,
			'auto_fix'   => false,
			'suggestion' => __( 'Use browser tools or specialized contrast checkers to verify text has at least 4.5:1 contrast ratio (3:1 for large text). Enable auto-fix in settings to add enhanced focus indicators.', 'plugin-wp-support-thisismyurl' ),
		);

		return $issues;
	}

	/**
	 * Check for focus order issues.
	 *
	 * @return array<int, array<string, mixed>> List of issues.
	 */
	private function check_focus_order(): array {
		$issues = array();

		// Check if there are focus management styles in theme.
		$issues[] = array(
			'type'       => 'focus_order',
			'severity'   => 'info',
			'message'    => __( 'Focus visibility check recommended', 'plugin-wp-support-thisismyurl' ),
			'element'    => __( 'Site-wide', 'plugin-wp-support-thisismyurl' ),
			'post_id'    => 0,
			'auto_fix'   => true,
			'fix_action' => 'add_focus_styles',
			'suggestion' => __( 'Ensure all interactive elements have visible focus indicators. Enable auto-fix in settings to add enhanced focus styles.', 'plugin-wp-support-thisismyurl' ),
		);

		return $issues;
	}

	/**
	 * Apply a specific fix to content.
	 *
	 * @param string $fix_type Type of fix to apply.
	 * @param int    $post_id  Post ID to fix.
	 * @return bool True if fix was applied successfully.
	 */
	private function apply_fix( string $fix_type, int $post_id ): bool {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		$content  = $post->post_content;
		$modified = false;

		switch ( $fix_type ) {
			case 'add_alt_attribute':
				$content  = $this->fix_missing_alt( $content );
				$modified = true;
				break;

			case 'remove_tabindex':
				// Remove positive tabindex values. Backreference \1 matches the captured quote.
				$content  = preg_replace( '/\s*\btabindex=(["\']?)[1-9][0-9]*\1/i', '', $content );
				$modified = true;
				break;

			case 'add_descriptive_alt':
				$content  = $this->fix_empty_alt( $content );
				$modified = true;
				break;
		}

		if ( $modified ) {
			return (bool) wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => $content,
				)
			);
		}

		return false;
	}

	/**
	 * Auto-fix images in content (filter).
	 *
	 * @param string $content Post content.
	 * @return string Modified content.
	 */
	public function auto_fix_images_in_content( string $content ): string {
		$content = $this->fix_missing_alt( $content );
		$content = $this->fix_empty_alt( $content );
		return $content;
	}

	/**
	 * Fix missing alt attributes.
	 *
	 * @param string $content Content to fix.
	 * @return string Fixed content.
	 */
	private function fix_missing_alt( string $content ): string {
		// Add empty alt to images without alt attribute (assumes decorative).
		// Negative lookahead (?![^>]*\balt=) ensures the img tag doesn't already have an alt attribute.
		return preg_replace( '/<img\b(?![^>]*\balt=)([^>]*?)(\s*\/?>)/i', '<img$1 alt=""$2', $content );
	}

	/**
	 * Fix empty alt attributes without decorative role.
	 *
	 * @param string $content Content to fix.
	 * @return string Fixed content.
	 */
	private function fix_empty_alt( string $content ): string {
		// Add role="presentation" to images with empty alt (no text between quotes) that don't have a role.
		// This only matches truly empty alt attributes (alt="" or alt='')
		// Backreference \2 captures the quote character (either " or '), and \2 is used again to match
		// the same closing quote, ensuring we only match empty quotes like alt="" or alt=''
		return preg_replace(
			'/<img\b([^>]*?)\balt=(["\'])\2(?![^>]*\brole=)([^>]*?)>/i',
			'<img$1alt=$2$2 role="presentation"$3>',
			$content
		);
	}

	/**
	 * Auto-fix ARIA in content (filter).
	 *
	 * @param string $content Post content.
	 * @return string Modified content.
	 */
	public function auto_fix_aria_in_content( string $content ): string {
		// Remove positive tabindex values (both quoted and unquoted).
		// Backreference \1 (or \\1 in replacement context) refers to the optional quote captured in group 1.
		$content = preg_replace( '/\s*\btabindex=(["\']?)[1-9][0-9]*\1/i', '', $content );
		return $content;
	}

	/**
	 * Add contrast fixes via CSS.
	 *
	 * @return void
	 */
	public function add_contrast_fixes(): void {
		?>
		<style id="wps-a11y-contrast-fixes">
		/* WPS Accessibility: Enhanced contrast */
		a:not(.no-contrast-fix) {
			text-decoration: underline;
		}
		
		a:hover:not(.no-contrast-fix),
		a:focus:not(.no-contrast-fix) {
			text-decoration-thickness: 2px;
		}
		</style>
		<?php
	}

	/**
	 * Add focus indicators via CSS and JS.
	 *
	 * @return void
	 */
	public function add_focus_indicators(): void {
		?>
		<style id="wps-a11y-focus-indicators">
		/* WPS Accessibility: Enhanced focus indicators */
		a:focus,
		button:focus,
		input:focus,
		select:focus,
		textarea:focus,
		[tabindex]:focus {
			outline: 3px solid #4A90E2 !important;
			outline-offset: 2px !important;
		}

		a:focus:not(:focus-visible),
		button:focus:not(:focus-visible),
		input:focus:not(:focus-visible),
		select:focus:not(:focus-visible),
		textarea:focus:not(:focus-visible),
		[tabindex]:focus:not(:focus-visible) {
			outline: none !important;
		}
		</style>
		<?php
	}

	/**
	 * Get plugin options.
	 *
	 * @return array<string, mixed> Options array.
	 */
	private function get_options(): array {
		return (array) get_option(
			'wps_a11y_audit_options',
			array(
				'auto_fix_images'   => false,
				'auto_fix_contrast' => false,
				'auto_fix_focus'    => true,
				'auto_fix_aria'     => false,
			)
		);
	}
}
