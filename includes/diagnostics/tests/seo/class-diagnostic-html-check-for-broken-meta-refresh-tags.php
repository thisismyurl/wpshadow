<?php
/**
 * HTML Check For Broken Meta Refresh Tags Diagnostic
 *
 * Detects broken meta refresh tag syntax.
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
 * HTML Check For Broken Meta Refresh Tags Diagnostic Class
 *
 * Identifies pages with malformed or broken meta refresh tags
 * that may not function correctly.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Check_For_Broken_Meta_Refresh_Tags extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-check-for-broken-meta-refresh-tags';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Meta Refresh Tags';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects malformed or broken meta refresh tag syntax';

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

		$broken_refreshes = array();

		// Check scripts for broken meta refresh patterns.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Pattern 1: Meta refresh without URL.
					if ( preg_match( '/<meta[^>]*http-equiv=["\']?refresh["\']?[^>]*>/i', $data, $m ) ) {
						$tag = $m[0];

						// Check if URL is missing from content attribute.
						if ( ! preg_match( '/url\s*=/i', $tag ) ) {
							$broken_refreshes[] = array(
								'handle' => $handle,
								'tag'    => substr( $tag, 0, 80 ),
								'issue'  => __( 'Meta refresh tag missing URL in content attribute', 'wpshadow' ),
							);
						}

						// Pattern 2: Invalid URL format.
						if ( preg_match( '/content=["\']([^"\']*)["\']/', $tag, $m ) ) {
							$content = $m[1];

							// Check for malformed URL.
							if ( ! preg_match( '/\d+;?\s*url\s*=/i', $content ) && strpos( $content, 'url' ) !== false ) {
								$broken_refreshes[] = array(
									'handle'  => $handle,
									'content' => $content,
									'issue'   => __( 'Meta refresh content attribute has invalid format', 'wpshadow' ),
								);
							}
						}
					}

					// Pattern 3: Missing http-equiv attribute.
					if ( preg_match( '/<meta[^>]*content=["\']([^"\']*refresh[^"\']*)["\']/', $data ) ) {
						if ( ! preg_match( '/http-equiv/i', $data ) ) {
							$broken_refreshes[] = array(
								'handle' => $handle,
								'issue'  => __( 'Meta refresh tag missing http-equiv="refresh" attribute', 'wpshadow' ),
							);
						}
					}

					// Pattern 4: Unclosed meta tag.
					if ( preg_match( '/<meta[^>]*refresh[^>]*(?<!>)$/', $data ) ) {
						$broken_refreshes[] = array(
							'handle' => $handle,
							'issue'  => __( 'Meta refresh tag appears to be unclosed', 'wpshadow' ),
						);
					}

					// Pattern 5: Multiple conflicting refresh tags.
					$refresh_count = preg_match_all( '/<meta[^>]*http-equiv=["\']?refresh["\']?/i', $data );

					if ( $refresh_count > 1 ) {
						$broken_refreshes[] = array(
							'handle' => $handle,
							'count'  => $refresh_count,
							'issue'  => sprintf(
								__( '%d conflicting meta refresh tags found on page', 'wpshadow' ),
								$refresh_count
							),
						);
					}
				}
			}
		}

		if ( empty( $broken_refreshes ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $broken_refreshes, 0, $max_items ) as $issue ) {
			$items_list .= sprintf(
				"\n- %s: %s",
				esc_html( $issue['handle'] ),
				esc_html( $issue['issue'] )
			);
		}

		if ( count( $broken_refreshes ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more broken refresh tags", 'wpshadow' ),
				count( $broken_refreshes ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d broken meta refresh tag(s). Broken syntax prevents redirects from working. Meta refresh tag should be: <meta http-equiv="refresh" content="delay; url=destination" />. Better yet, use HTTP redirects instead.%2$s', 'wpshadow' ),
				count( $broken_refreshes ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-check-for-broken-meta-refresh-tags',
			'meta'         => array(
				'broken' => $broken_refreshes,
			),
		);
	}
}
