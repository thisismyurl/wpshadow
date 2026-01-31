<?php
/**
 * Comment HTML Tag Whitelist Diagnostic
 *
 * Verifies allowed HTML tags in comments are properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment HTML Tag Whitelist Diagnostic Class
 *
 * @since 1.26031.1300
 */
class Diagnostic_Comment_HTML_Tag_Whitelist extends Diagnostic_Base {

	protected static $slug = 'comment-html-tag-whitelist';
	protected static $title = 'Comment HTML Tag Whitelist';
	protected static $description = 'Verifies allowed HTML tags in comments properly configured';
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26031.1300
	 * @return array|null
	 */
	public static function check() {
		global $allowedtags;

		$issues = array();

		// Check for dangerous tags.
		$dangerous_tags = array( 'script', 'iframe', 'object', 'embed', 'form', 'input' );
		foreach ( $dangerous_tags as $tag ) {
			if ( isset( $allowedtags[ $tag ] ) ) {
				$issues[] = array(
					'tag'         => $tag,
					'description' => sprintf(
						/* translators: %s: HTML tag name */
						__( 'Dangerous HTML tag allowed in comments: <%s>', 'wpshadow' ),
						$tag
					),
					'severity'    => 'critical',
				);
			}
		}

		// Check for overly permissive attributes.
		$risky_attrs = array( 'onclick', 'onload', 'onerror', 'onmouseover', 'style' );
		foreach ( $allowedtags as $tag => $attrs ) {
			if ( is_array( $attrs ) ) {
				foreach ( $risky_attrs as $attr ) {
					if ( isset( $attrs[ $attr ] ) ) {
						$issues[] = array(
							'tag'         => $tag,
							'attribute'   => $attr,
							'description' => sprintf(
								/* translators: 1: attribute name, 2: HTML tag */
								__( 'Risky attribute %1$s allowed on <%2$s> tag in comments', 'wpshadow' ),
								$attr,
								$tag
							),
							'severity'    => 'high',
						);
					}
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d comment HTML tag security issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 50,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/comment-html-tag-whitelist',
		);
	}
}
