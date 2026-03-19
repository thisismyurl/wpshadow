<?php
/**
 * Tabnabbing Attack Not Prevented Diagnostic
 *
 * Checks external links for tabnabbing protection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Tabnabbing_Attack_Not_Prevented Class
 *
 * Performs diagnostic check for tabnabbing protection.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Tabnabbing_Attack_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tabnabbing-attack-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tabnabbing Attack Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks external links for missing noopener/noreferrer protection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_external_links_missing_noopener();

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of external links missing rel noopener/noreferrer */
				__( 'Found %d external link(s) opening a new tab without noopener protection', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'issues'       => array_slice( $issues, 0, 10 ),
			'total_issues' => count( $issues ),
			'user_impact'  => __( 'External sites can take control of the original tab when noopener is missing, which can lead to phishing.', 'wpshadow' ),
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/tabnabbing-attack-not-prevented',
		);
	}

	/**
	 * Find external links opening in new tabs without noopener protection.
	 *
	 * @since 1.6093.1200
	 * @return array Missing rel attribute issues.
	 */
	private static function find_external_links_missing_noopener(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array();
		}

		$dom = Diagnostic_HTML_Helper::parse_html( $html );
		if ( ! $dom ) {
			return array();
		}

		$xpath = Diagnostic_HTML_Helper::create_xpath( $dom );
		$links = $xpath->query( '//a[@href]' );
		if ( ! $links ) {
			return array();
		}

		$issues = array();
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );

		foreach ( $links as $link ) {
			if ( ! $link instanceof \DOMElement ) {
				continue;
			}

			$href = trim( $link->getAttribute( 'href' ) );
			$target = strtolower( $link->getAttribute( 'target' ) );
			$rel = strtolower( $link->getAttribute( 'rel' ) );

			if ( '' === $href || ! self::is_external_link( $href, $site_host ) ) {
				continue;
			}

			if ( '_blank' !== $target ) {
				continue;
			}

			if ( self::has_noopener_protection( $rel ) ) {
				continue;
			}

			$issues[] = array(
				'type'   => 'missing-noopener',
				'link'   => esc_url_raw( $href ),
				'issue'  => __( 'External link opens in a new tab without rel="noopener noreferrer"', 'wpshadow' ),
				'impact' => __( 'Without noopener, the external site can redirect your original tab.', 'wpshadow' ),
			);
		}

		return $issues;
	}

	/**
	 * Determine if a link is external.
	 *
	 * @since 1.6093.1200
	 * @param  string $href Link URL.
	 * @param  string $site_host Site host name.
	 * @return bool True if external.
	 */
	private static function is_external_link( string $href, string $site_host ): bool {
		if ( 0 === strpos( $href, '#' ) || 0 === strpos( $href, '/' ) ) {
			return false;
		}

		$host = wp_parse_url( $href, PHP_URL_HOST );
		if ( empty( $host ) || empty( $site_host ) ) {
			return false;
		}

		return $host !== $site_host;
	}

	/**
	 * Check if rel attribute includes noopener and noreferrer.
	 *
	 * @since 1.6093.1200
	 * @param  string $rel Rel attribute value.
	 * @return bool True when protection is present.
	 */
	private static function has_noopener_protection( string $rel ): bool {
		if ( '' === $rel ) {
			return false;
		}

		return false !== strpos( $rel, 'noopener' ) && false !== strpos( $rel, 'noreferrer' );
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since 1.6093.1200
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
