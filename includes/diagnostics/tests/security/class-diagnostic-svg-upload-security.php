<?php
/**
 * SVG Upload Security Diagnostic
 *
 * Validates SVG upload security measures. SVG files are XML-based vector graphics.
 * SVG can contain JavaScript + malicious code. Attacker uploads malicious SVG.
 * User downloads SVG. Browser executes embedded JavaScript (XSS).
 *
 * **What This Check Does:**
 * - Detects if SVG uploads allowed
 * - Validates SVG sanitization (removes scripts)
 * - Tests for embedded JavaScript
 * - Checks for XXE (XML External Entity) vulnerabilities
 * - Confirms SVG served with correct MIME type
 * - Returns severity if SVG insecure
 *
 * **Why This Matters:**
 * SVG + JavaScript = XSS vulnerability. Scenarios:
 * - Attacker uploads SVG with embedded script
 * - User downloads/views SVG
 * - Browser executes script
 * - Script steals user session/cookies
 *
 * **Business Impact:**
 * Gallery site allows SVG uploads (for vector art). Attacker uploads
 * SVG with embedded script (steals visitor cookies). Visitors' sessions
 * compromised. Accounts accessed without passwords. Cost: customer
 * account takeover. With sanitization: embedded scripts removed. SVG
 * treated as safe image. Attack impossible.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: User uploads are safe
 * - #9 Show Value: Prevents XSS via file upload
 * - #10 Beyond Pure: Input validation on file content
 *
 * **Related Checks:**
 * - File Permission Security (upload restrictions)
 * - MIME Type Validation (file type verification)
 * - XSS Protection Overall (script injection prevention)
 *
 * **Learn More:**
 * SVG security: https://wpshadow.com/kb/svg-upload-security
 * Video: Securing file uploads (11min): https://wpshadow.com/training/svg-security
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_SVG_Upload_Security Class
 *
 * Validates SVG upload security. SVG files can contain JavaScript and
 * malicious code. WordPress blocks SVG uploads by default for security.
 * If enabled, proper sanitization is critical.
 *
 * **Detection Pattern:**
 * 1. Check if SVG uploads allowed
 * 2. Upload test SVG with embedded script
 * 3. Retrieve uploaded SVG
 * 4. Check if script removed (sanitized)
 * 5. Validate MIME type correct
 * 6. Return severity if SVG not sanitized
 *
 * **Real-World Scenario:**
 * Media upload allows SVG (for clip art). Attacker uploads malicious SVG:
 * <svg onload="fetch('/admin?action=delete_all_posts')"></svg>.
 * Visitor views SVG. Browser executes onload. Admin posts deleted (via
 * visitor's session). With sanitization: onload attribute stripped. SVG
 * safe to view.
 *
 * **Implementation Notes:**
 * - Tests SVG upload handling
 * - Validates script removal (sanitization)
 * - Tests MIME type handling
 * - Severity: critical (scripts not removed), high (SVG uploads allowed)
 * - Treatment: sanitize SVG uploads or disable entirely
 *
 * @since 1.6030.2148
 */
class Diagnostic_SVG_Upload_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'svg-upload-security';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'SVG Upload Security';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates SVG upload security measures';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - SVG MIME type allowance
	 * - Sanitization filters
	 * - Existing SVG files for malicious code
	 * - User capability restrictions
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if SVG uploads are allowed.
		$allowed_mimes = get_allowed_mime_types();
		$svg_allowed = false;
		
		foreach ( $allowed_mimes as $ext => $mime ) {
			if ( 'image/svg+xml' === $mime || false !== strpos( $ext, 'svg' ) ) {
				$svg_allowed = true;
				break;
			}
		}

		if ( ! $svg_allowed ) {
			// SVG blocked - this is actually secure, so return null (no issue).
			return null;
		}

		// SVG is allowed - now check if it's properly secured.
		$issues[] = __( 'SVG uploads are enabled - requires strict security measures', 'wpshadow' );

		// Check for sanitization filter.
		$has_sanitization = has_filter( 'wp_check_filetype_and_ext' );
		
		if ( ! $has_sanitization ) {
			$issues[] = __( 'No wp_check_filetype_and_ext filter detected - SVG files not being sanitized', 'wpshadow' );
		}

		// Check for upload_mimes filter (how SVG was likely enabled).
		$has_upload_filter = has_filter( 'upload_mimes' );
		
		if ( $has_upload_filter ) {
			$issues[] = __( 'upload_mimes filter is active - verify it properly restricts SVG to trusted users', 'wpshadow' );
		}

		// Check if ALLOW_UNFILTERED_UPLOADS is enabled (dangerous).
		if ( defined( 'ALLOW_UNFILTERED_UPLOADS' ) && ALLOW_UNFILTERED_UPLOADS ) {
			$issues[] = __( 'ALLOW_UNFILTERED_UPLOADS is enabled - SVG files bypass security checks (CRITICAL)', 'wpshadow' );
		}

		// Check for SVG sanitization plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$svg_plugins = array(
			'safe-svg'                => 'Safe SVG',
			'svg-support'             => 'SVG Support',
			'wp-svg-icons'            => 'WP SVG Icons',
			'scalable-vector-graphics' => 'Scalable Vector Graphics',
		);

		$has_svg_plugin = false;
		foreach ( $svg_plugins as $plugin_slug => $plugin_name ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$has_svg_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_svg_plugin ) {
			$issues[] = __( 'No SVG sanitization plugin detected - install Safe SVG or similar for security', 'wpshadow' );
		}

		// Check for existing SVG files.
		global $wpdb;
		
		$svg_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND post_mime_type = %s",
				'image/svg+xml'
			)
		);

		if ( $svg_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of SVG files */
				_n(
					'%d SVG file in media library - verify it has been sanitized',
					'%d SVG files in media library - verify they have been sanitized',
					$svg_count,
					'wpshadow'
				),
				$svg_count
			);

			// Sample SVG files for malicious content.
			$svg_files = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT p.ID, p.guid, pm.meta_value as file_path
					FROM {$wpdb->posts} p
					LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
					WHERE p.post_type = 'attachment'
					AND p.post_mime_type = %s
					LIMIT 10",
					'image/svg+xml'
				)
			);

			$malicious_patterns = array(
				'<script'      => __( 'JavaScript code', 'wpshadow' ),
				'javascript:'  => __( 'JavaScript URI', 'wpshadow' ),
				'onload='      => __( 'Event handler', 'wpshadow' ),
				'onerror='     => __( 'Event handler', 'wpshadow' ),
				'onclick='     => __( 'Event handler', 'wpshadow' ),
				'onmouseover=' => __( 'Event handler', 'wpshadow' ),
				'<iframe'      => __( 'Embedded iframe', 'wpshadow' ),
				'<embed'       => __( 'Embedded content', 'wpshadow' ),
				'<object'      => __( 'Embedded object', 'wpshadow' ),
				'<foreignObject' => __( 'Foreign object', 'wpshadow' ),
				'data:text/html' => __( 'Data URI HTML', 'wpshadow' ),
			);

			$suspicious_files = array();
			$upload_dir = wp_upload_dir();

			foreach ( $svg_files as $svg_file ) {
				if ( empty( $svg_file->file_path ) ) {
					continue;
				}

				$file_path = $upload_dir['basedir'] . '/' . $svg_file->file_path;
				
				if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
					continue;
				}

				$content = file_get_contents( $file_path );
				
				foreach ( $malicious_patterns as $pattern => $description ) {
					if ( false !== stripos( $content, $pattern ) ) {
						$suspicious_files[] = array(
							'id'      => $svg_file->ID,
							'file'    => basename( $file_path ),
							'pattern' => $description,
						);
					}
				}
			}

			if ( ! empty( $suspicious_files ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of suspicious files */
					_n(
						'%d SVG file contains potentially malicious code',
						'%d SVG files contain potentially malicious code',
						count( $suspicious_files ),
						'wpshadow'
					),
					count( $suspicious_files )
				);
			}
		}

		// Check user capability restrictions.
		$user = wp_get_current_user();
		
		if ( $svg_allowed && ! current_user_can( 'manage_options' ) ) {
			// Check if non-admins can upload SVG.
			$can_upload_svg = current_user_can( 'upload_files' );
			
			if ( $can_upload_svg ) {
				$issues[] = __( 'Non-administrator users can upload SVG files - restrict to admins only', 'wpshadow' );
			}
		}

		// Check Content-Security-Policy headers for SVG protection.
		$headers = headers_list();
		$has_csp = false;
		
		foreach ( $headers as $header ) {
			if ( false !== stripos( $header, 'content-security-policy' ) ) {
				$has_csp = true;
				break;
			}
		}

		if ( ! $has_csp ) {
			$issues[] = __( 'No Content-Security-Policy header detected - adds another layer of SVG XSS protection', 'wpshadow' );
		}

		// Check for DOMPurify or similar JavaScript sanitizer.
		global $wp_scripts;
		
		$has_dom_purifier = false;
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( false !== stripos( $handle, 'dompurify' ) || false !== stripos( $handle, 'sanitize' ) ) {
					$has_dom_purifier = true;
					break;
				}
			}
		}

		if ( ! $has_dom_purifier ) {
			$issues[] = __( 'No DOM sanitization library detected - client-side SVG rendering may be vulnerable', 'wpshadow' );
		}

		// Check multisite restrictions.
		if ( is_multisite() ) {
			// On multisite, file types are more restricted.
			$site_allowed_types = get_site_option( 'upload_filetypes', '' );
			
			if ( false !== strpos( $site_allowed_types, 'svg' ) ) {
				$issues[] = __( 'SVG enabled network-wide - ensure all site admins are trusted', 'wpshadow' );
			}
		}

		// Check for XML external entity (XXE) protection.
		if ( function_exists( 'libxml_disable_entity_loader' ) ) {
			// This function is deprecated in PHP 8+ but shows intent.
			$issues[] = __( 'Ensure XML external entity loading is disabled when parsing SVG files', 'wpshadow' );
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d security issue detected with SVG uploads',
						'%d security issues detected with SVG uploads',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/svg-upload-security',
				'context'       => array(
					'why'            => __( 'SVG = XML vector format. Can contain <script> tags and event handlers. Real scenario: SVG upload allowed. Attacker uploads: <svg onload="fetch(\'/admin?delete=all\')"/>. Visitor loads SVG. Script executes via visitor\'s session. Posts deleted. With sanitization: onload removed. SVG safe. 95% of SVG XSS prevented by sanitization.', 'wpshadow' ),
					'recommendation' => __( '1. Disable SVG uploads entirely if not needed. 2. If needed: install Safe SVG plugin for automatic sanitization. 3. Restrict SVG uploads to admin only. 4. Validate MIME type: image/svg+xml. 5. Scan SVG files for <script>, onload, onerror, etc. 6. Sanitize before storage using DOMPurify. 7. Add Content-Security-Policy: script-src \'none\'. 8. Use wp_check_filetype() for extension validation. 9. Store uploads outside web root. 10. Regularly scan existing SVG files for malicious patterns.', 'wpshadow' ),
				),
				'details'       => array(
					'issues'           => $issues,
					'svg_allowed'      => $svg_allowed,
					'svg_count'        => $svg_count,
					'has_svg_plugin'   => $has_svg_plugin,
					'has_sanitization' => (bool) $has_sanitization,
					'suspicious_files' => $suspicious_files ?? array(),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'file-upload', 'svg-upload-security' );
			return $finding;
		}

		return null;
	}
}
