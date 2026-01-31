<?php
/**
 * SVG Files Not Sanitized Diagnostic
 *
 * Detects if SVG uploads are properly sanitized to prevent
 * malicious script injection through SVG files (XSS vulnerability).
 *
 * @since   1.6028.1520
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_SVG_Sanitization Class
 *
 * Checks if SVG file uploads are sanitized or restricted to prevent
 * XSS attacks via malicious SVG content.
 *
 * @since 1.6028.1520
 */
class Diagnostic_SVG_Sanitization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'svg-files-not-sanitized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SVG Files Not Sanitized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects unsanitized SVG uploads that allow malicious script injection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if SVG uploads are enabled and if proper sanitization
	 * is in place to prevent XSS attacks.
	 *
	 * @since  1.6028.1520
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$svg_status = self::check_svg_security();

		if ( $svg_status['sanitized'] || ! $svg_status['enabled'] ) {
			return null; // SVG uploads are sanitized or disabled - safe
		}

		// SVG uploads enabled without sanitization - critical security issue
		$existing_svgs = self::count_svg_uploads();

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: number of SVG files */
				__( 'Your site allows SVG uploads without proper sanitization. %d SVG files could potentially contain malicious scripts that execute when viewed. This is a critical XSS vulnerability.', 'wpshadow' ),
				$existing_svgs
			),
			'severity'      => 'high',
			'threat_level'  => 75,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/svg-security',
			'family'        => self::$family,
			'meta'          => array(
				'existing_svgs'     => $existing_svgs,
				'svg_enabled'       => $svg_status['enabled'],
				'has_sanitization'  => $svg_status['sanitized'],
				'attack_vector'     => 'XSS via SVG',
				'impact_level'      => __( 'High - XSS vulnerability allows script execution', 'wpshadow' ),
				'immediate_actions' => array(
					__( 'Install Safe SVG plugin immediately', 'wpshadow' ),
					__( 'Or disable SVG uploads until sanitization is in place', 'wpshadow' ),
					__( 'Audit existing SVG files for malicious content', 'wpshadow' ),
					__( 'Restrict SVG uploads to trusted administrators only', 'wpshadow' ),
				),
			),
			'details'       => array(
				'why_important'    => __( 'SVG files can contain JavaScript and other executable code. Without proper sanitization, attackers can upload malicious SVG files that execute scripts when any user views them, stealing cookies, session tokens, or performing actions on behalf of users. This is a stored XSS vulnerability that affects all site visitors.', 'wpshadow' ),
				'user_impact'      => array(
					__( 'Visitors: Malicious scripts execute in their browsers', 'wpshadow' ),
					__( 'Administrators: Session hijacking and account takeover', 'wpshadow' ),
					__( 'Site Security: Stored XSS affects all users who view the SVG', 'wpshadow' ),
					__( 'Data Theft: Cookies and tokens can be stolen', 'wpshadow' ),
				),
				'attack_scenario'  => array(
					'Step 1' => __( 'Attacker uploads SVG with embedded JavaScript', 'wpshadow' ),
					'Step 2' => __( 'SVG is stored in media library without sanitization', 'wpshadow' ),
					'Step 3' => __( 'Victim views page/post containing the SVG', 'wpshadow' ),
					'Step 4' => __( 'JavaScript executes with victim\'s permissions', 'wpshadow' ),
					'Step 5' => __( 'Attacker steals session or performs malicious actions', 'wpshadow' ),
				),
				'solution_options' => array(
					'Safe SVG (Recommended)' => array(
						'description' => __( 'Install Safe SVG plugin for automatic sanitization', 'wpshadow' ),
						'time'        => __( '5 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'plugin'      => 'safe-svg',
						'steps'       => array(
							__( 'Install Safe SVG from WordPress.org', 'wpshadow' ),
							__( 'Activate plugin', 'wpshadow' ),
							__( 'Configure who can upload SVGs (restrict to admins)', 'wpshadow' ),
							__( 'Test by uploading a test SVG', 'wpshadow' ),
						),
					),
					'Disable SVG Uploads' => array(
						'description' => __( 'Block SVG uploads entirely via functions.php', 'wpshadow' ),
						'time'        => __( '5 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'code'        => "add_filter('upload_mimes', function(\$mimes) { unset(\$mimes['svg']); return \$mimes; });",
					),
					'Custom Sanitization' => array(
						'description' => __( 'Implement DOMPurify-based SVG sanitization', 'wpshadow' ),
						'time'        => __( '2-3 hours', 'wpshadow' ),
						'cost'        => __( 'Free (developer time)', 'wpshadow' ),
						'difficulty'  => __( 'Advanced', 'wpshadow' ),
					),
				),
				'best_practices'   => array(
					__( 'Always sanitize SVG uploads using a library like DOMPurify', 'wpshadow' ),
					__( 'Restrict SVG upload capability to administrators only', 'wpshadow' ),
					__( 'Use Safe SVG or similar plugin for automatic protection', 'wpshadow' ),
					__( 'Audit existing SVG files for malicious content', 'wpshadow' ),
					__( 'Consider using PNG/JPG alternatives for non-interactive graphics', 'wpshadow' ),
					__( 'Never trust user-uploaded SVG content', 'wpshadow' ),
				),
				'testing_steps'    => array(
					'Step 1' => __( 'Install Safe SVG plugin', 'wpshadow' ),
					'Step 2' => __( 'Test uploading a clean SVG file', 'wpshadow' ),
					'Step 3' => __( 'Try uploading SVG with <script> tag (should be stripped)', 'wpshadow' ),
					'Step 4' => __( 'Verify only admins can upload SVGs', 'wpshadow' ),
					'Step 5' => __( 'Check existing SVGs for suspicious content', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Check SVG upload security status.
	 *
	 * Determines if SVG uploads are enabled and if sanitization is active.
	 *
	 * @since  1.6028.1520
	 * @return array SVG security status.
	 */
	private static function check_svg_security() {
		$status = array(
			'enabled'    => false,
			'sanitized'  => false,
		);

		// Check if SVG MIME type is allowed
		$allowed_mimes = get_allowed_mime_types();
		$status['enabled'] = isset( $allowed_mimes['svg'] ) || isset( $allowed_mimes['svgz'] );

		if ( ! $status['enabled'] ) {
			return $status; // SVG uploads disabled - safe
		}

		// Check for SVG sanitization plugins
		$sanitization_plugins = array(
			'safe-svg/safe-svg.php',
			'svg-support/svg-support.php',
			'wp-svg-icons/wp-svg-icons.php',
		);

		foreach ( $sanitization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$status['sanitized'] = true;
				break;
			}
		}

		// Check if custom sanitization exists
		if ( has_filter( 'wp_handle_upload_prefilter' ) ) {
			// Conservative: assume custom sanitization might exist
			// but flag for manual review
		}

		return $status;
	}

	/**
	 * Count existing SVG files in media library.
	 *
	 * @since  1.6028.1520
	 * @return int Number of SVG files.
	 */
	private static function count_svg_uploads() {
		global $wpdb;

		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type IN ('image/svg+xml', 'image/svg')"
		);

		return intval( $count );
	}
}
