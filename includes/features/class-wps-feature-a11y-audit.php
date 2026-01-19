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
			'name'        => __( 'Accessibility Audit', 'wpshadow' ),
			'description' => __( 'Detect and fix WCAG accessibility violations.', 'wpshadow' ),
			'sub_features' => array(
				'alt_text_check'     => __( 'Missing Alt Text', 'wpshadow' ),
				'aria_validation'    => __( 'ARIA Validation', 'wpshadow' ),
				'keyboard_navigation' => __( 'Keyboard Navigation', 'wpshadow' ),
				'contrast_checking'  => __( 'Contrast Ratio Check', 'wpshadow' ),
				'auto_fixes'         => __( 'Auto-Fix Issues', 'wpshadow' ),
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

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Check for accessibility issues in posts.
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
				__( '%d issues detected. %d sub-features enabled.', 'wpshadow' ),
				count( $issues ),
				$enabled_count
			),
			'actions'     => '',
			'test'        => 'a11y_audit',
		);
	}
}
