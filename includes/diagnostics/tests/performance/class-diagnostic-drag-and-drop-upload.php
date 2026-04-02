<?php
/**
 * Drag-and-Drop Upload Diagnostic
 *
 * Validates drag-and-drop upload functionality in media library.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Drag_And_Drop_Upload Class
 *
 * Validates drag-and-drop upload functionality. WordPress uses Plupload's
 * drop zone feature for drag-and-drop. Issues with HTML5 FileAPI or drop
 * zone configuration can break this feature.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Drag_And_Drop_Upload extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'drag-and-drop-upload';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Drag-and-Drop Upload';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates drag-and-drop upload functionality in media library';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Plupload drop zone configuration
	 * - HTML5 File API support requirements
	 * - Conflicting JavaScript handlers
	 * - CSS that might hide drop zones
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check Plupload settings for drop zone configuration.
		$plupload_settings = wp_plupload_default_settings();

		// Drop element should be configured.
		if ( empty( $plupload_settings['drop_element'] ) ) {
			$issues[] = __( 'Plupload drop_element not configured - drag-and-drop disabled', 'wpshadow' );
		}

		// HTML5 runtime must be available for drag-and-drop.
		if ( ! empty( $plupload_settings['runtimes'] ) ) {
			$runtimes = explode( ',', $plupload_settings['runtimes'] );
			$runtimes = array_map( 'trim', $runtimes );
			
			if ( ! in_array( 'html5', $runtimes, true ) ) {
				$issues[] = __( 'HTML5 runtime not enabled - required for drag-and-drop', 'wpshadow' );
			}
		}

		// Check if required scripts are loaded.
		global $wp_scripts;
		
		$required_scripts = array(
			'plupload'         => __( 'Core Plupload library', 'wpshadow' ),
			'plupload-html5'   => __( 'Plupload HTML5 runtime', 'wpshadow' ),
			'wp-plupload'      => __( 'WordPress Plupload integration', 'wpshadow' ),
			'media-upload'     => __( 'Media upload handler', 'wpshadow' ),
		);

		foreach ( $required_scripts as $handle => $name ) {
			if ( ! wp_script_is( $handle, 'registered' ) ) {
				$issues[] = sprintf(
					/* translators: %s: script name */
					__( 'Required script not registered: %s', 'wpshadow' ),
					$name
				);
			}
		}

		// Check for jQuery (required for drag-and-drop events).
		if ( ! wp_script_is( 'jquery', 'registered' ) ) {
			$issues[] = __( 'jQuery not registered - required for drag-and-drop events', 'wpshadow' );
		}

		// Check for conflicting drag/drop event handlers.
		// Some plugins prevent default drag/drop which breaks uploads.
		$active_plugins = get_option( 'active_plugins', array() );
		$known_conflicts = array(
			'drag-drop-featured-image' => __( 'May conflict with media library drag-and-drop', 'wpshadow' ),
			'simple-image-sizes'       => __( 'May interfere with drag-and-drop handlers', 'wpshadow' ),
		);

		foreach ( $known_conflicts as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = sprintf(
						/* translators: %s: conflict message */
						__( 'Plugin conflict: %s', 'wpshadow' ),
						$message
					);
					break;
				}
			}
		}

		// Check for browser restrictions.
		if ( isset( $plupload_settings['filters'] ) && isset( $plupload_settings['filters']['mime_types'] ) ) {
			// If mime_types is empty, drag-and-drop might be disabled.
			$mime_types = $plupload_settings['filters']['mime_types'];
			if ( empty( $mime_types ) ) {
				$issues[] = __( 'MIME type filters empty - may prevent drag-and-drop', 'wpshadow' );
			}
		}

		// Check upload directory permissions (affects drag-and-drop).
		$upload_dir = wp_upload_dir();
		if ( ! wp_is_writable( $upload_dir['path'] ) ) {
			$issues[] = sprintf(
				/* translators: %s: directory path */
				__( 'Upload directory not writable: %s', 'wpshadow' ),
				$upload_dir['path']
			);
		}

		// Check for CSS that might hide the drop zone.
		global $wp_styles;
		$has_media_css = false;
		
		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( false !== strpos( $handle, 'media' ) ) {
					$has_media_css = true;
					break;
				}
			}
		}

		if ( ! $has_media_css ) {
			$issues[] = __( 'Media library CSS not loaded - drop zone may not be visible', 'wpshadow' );
		}

		// Check for JavaScript errors that might break drag-and-drop.
		global $wpdb;
		
		// Check transients for JavaScript errors related to uploads.
		$js_errors = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND (option_value LIKE %s OR option_value LIKE %s)",
				$wpdb->esc_like( '_transient_' ) . '%',
				'%drag%',
				'%drop%'
			)
		);

		if ( $js_errors > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of errors */
				_n(
					'%d drag-and-drop related error found',
					'%d drag-and-drop related errors found',
					$js_errors,
					'wpshadow'
				),
				$js_errors
			);
		}

		// Check for security plugins blocking File API.
		$security_plugins = array(
			'wordfence'              => __( 'Wordfence - may block File API access', 'wpshadow' ),
			'better-wp-security'     => __( 'iThemes Security - may restrict File API', 'wpshadow' ),
			'all-in-one-wp-security' => __( 'All In One WP Security - may block drag-and-drop', 'wpshadow' ),
		);

		foreach ( $security_plugins as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = $message;
					break;
				}
			}
		}

		// Check for Content Security Policy headers that might block File API.
		$headers = headers_list();
		foreach ( $headers as $header ) {
			if ( false !== stripos( $header, 'content-security-policy' ) ) {
				// CSP can restrict File API access.
				if ( false === stripos( $header, 'blob:' ) ) {
					$issues[] = __( 'Content-Security-Policy missing blob: directive - may break drag-and-drop', 'wpshadow' );
				}
				break;
			}
		}

		// Check for HTTPS requirement (some browsers require SSL for File API).
		if ( ! is_ssl() && isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			// Chrome 50+ requires HTTPS for some File API features.
			if ( false !== strpos( $_SERVER['HTTP_USER_AGENT'], 'Chrome' ) ) {
				$issues[] = __( 'Site not using HTTPS - Chrome may restrict drag-and-drop on HTTP', 'wpshadow' );
			}
		}

		// Check for max_file_uploads limit.
		$max_file_uploads = (int) ini_get( 'max_file_uploads' );
		if ( $max_file_uploads > 0 && $max_file_uploads < 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				__( 'max_file_uploads (%d) is low - limits bulk drag-and-drop uploads', 'wpshadow' ),
				$max_file_uploads
			);
		}

		// Check for browse_button configuration.
		if ( empty( $plupload_settings['browse_button'] ) ) {
			$issues[] = __( 'Browse button not configured - fallback for drag-and-drop unavailable', 'wpshadow' );
		}

		// Check for multipart setting (affects large drag-and-drop uploads).
		if ( isset( $plupload_settings['multipart'] ) && ! $plupload_settings['multipart'] ) {
			$issues[] = __( 'Multipart disabled - large drag-and-drop uploads will fail', 'wpshadow' );
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d issue detected with drag-and-drop upload functionality',
						'%d issues detected with drag-and-drop upload functionality',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/drag-and-drop-upload',
				'details'      => array(
					'issues'            => $issues,
					'drop_element'      => $plupload_settings['drop_element'] ?? 'Not configured',
					'runtimes'          => $plupload_settings['runtimes'] ?? 'Not set',
					'ssl_enabled'       => is_ssl(),
					'js_errors'         => $js_errors,
					'max_file_uploads'  => $max_file_uploads,
				),
			);
		}

		return null;
	}
}
