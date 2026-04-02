<?php
/**
 * Media Attachment Page SEO Diagnostic
 *
 * Tests SEO optimization of media attachment pages by
 * checking indexing controls and title handling.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Attachment_Page_SEO Class
 *
 * Validates SEO controls for attachment pages.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Attachment_Page_SEO extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-attachment-page-seo';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Attachment Page SEO';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests SEO optimization of media attachment pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'rank-math/rank-math.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'autodescription/autodescription.php',
			'seopress/seopress.php',
		);

		$seo_active = false;
		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$seo_active = true;
				break;
			}
		}

		if ( ! $seo_active && ! has_filter( 'wp_robots' ) ) {
			$issues[] = __( 'No SEO controls detected for attachment pages; consider adding noindex or redirects', 'wpshadow' );
		}

		$attachment_post_type = get_post_type_object( 'attachment' );
		if ( $attachment_post_type && $attachment_post_type->publicly_queryable ) {
			$issues[] = __( 'Attachment pages are publicly queryable; ensure they are optimized or redirected to avoid thin content', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-attachment-page-seo',
			);
		}

		return null;
	}
}
