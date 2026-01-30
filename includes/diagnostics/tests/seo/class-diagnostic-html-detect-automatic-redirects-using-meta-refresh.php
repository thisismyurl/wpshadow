<?php
/**
 * HTML Detect Automatic Redirects Using Meta Refresh Diagnostic
 *
 * Detects automatic redirects using meta refresh.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect Automatic Redirects Using Meta Refresh Diagnostic Class
 *
 * Identifies pages using meta refresh for redirects, which is bad for
 * SEO and user experience. HTTP redirects are the proper method.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Automatic_Redirects_Using_Meta_Refresh extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-automatic-redirects-using-meta-refresh';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Automatic Redirects Using Meta Refresh';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects page redirects via meta refresh instead of proper HTTP redirects';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'html';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$meta_refresh_issues = array();

		// Check scripts for meta refresh patterns.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Pattern 1: Meta refresh tag.
					if ( preg_match( '/<meta[^>]*http-equiv=["\']?refresh["\']?[^>]*content=["\']([^"\']*)["\'][^>]*>/i', $data, $m ) ) {
						$content = $m[1];

						// Extract delay and URL.
						if ( preg_match( '/(\d+);?\s*url\s*=\s*(["\']?)([^"\']+)\2/i', $content, $url_m ) ) {
							$delay = $url_m[1];
							$url   = $url_m[3];

							$meta_refresh_issues[] = array(
								'handle'  => $handle,
								'url'     => $url,
								'delay'   => $delay,
								'issue'   => sprintf(
									__( 'Meta refresh redirect to %s after %s seconds', 'wpshadow' ),
									esc_url( $url ),
									$delay
								),
							);
						}
					}

					// Pattern 2: JavaScript-based meta refresh injection.
					if ( preg_match( '/meta.*refresh|refresh.*meta/', $data, $m ) ) {
						if ( preg_match( '/createElement.*meta|meta.*content.*refresh/', $data ) ) {
							$meta_refresh_issues[] = array(
								'handle' => $handle,
								'issue'  => __( 'Script dynamically creates meta refresh element', 'wpshadow' ),
								'type'   => 'dynamic',
							);
						}
					}
				}
			}
		}

		if ( empty( $meta_refresh_issues ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $meta_refresh_issues, 0, $max_items ) as $issue ) {
			if ( isset( $issue['url'] ) ) {
				$items_list .= sprintf(
					"\n- %s: %s",
					esc_html( $issue['handle'] ),
					esc_html( $issue['issue'] )
				);
			} else {
				$items_list .= sprintf(
					"\n- %s: %s",
					esc_html( $issue['handle'] ),
					esc_html( $issue['issue'] )
				);
			}
		}

		if ( count( $meta_refresh_issues ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more meta refresh redirects", 'wpshadow' ),
				count( $meta_refresh_issues ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d meta refresh redirect(s). Meta refresh is bad for SEO and user experience. Search engines may not properly index/redirect, and it causes delays. Use HTTP 301/302 redirects via wp_redirect() or .htaccess instead.%2$s', 'wpshadow' ),
				count( $meta_refresh_issues ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-automatic-redirects-using-meta-refresh',
			'meta'         => array(
				'refreshes' => $meta_refresh_issues,
			),
		);
	}
}
