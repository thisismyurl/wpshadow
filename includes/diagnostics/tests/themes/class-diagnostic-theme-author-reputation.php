<?php
/**
 * Theme Author Reputation Diagnostic
 *
 * Validates theme author credentials and reputation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Author Reputation Class
 *
 * Checks theme author against WordPress.org reputation metrics.
 * Low-reputation authors increase security risk.
 *
 * @since 1.5029.1700
 */
class Diagnostic_Theme_Author_Reputation extends Diagnostic_Base {

	protected static $slug        = 'theme-author-reputation';
	protected static $title       = 'Theme Author Reputation';
	protected static $description = 'Validates theme author credentials';
	protected static $family      = 'themes';

	public static function check() {
		$cache_key = 'wpshadow_theme_author_reputation';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$current_theme = wp_get_theme();
		$theme_slug = $current_theme->get_stylesheet();
		$author = $current_theme->get( 'Author' );
		$author_uri = $current_theme->get( 'AuthorURI' );

		$reputation_issues = array();

		// Check if theme is from WordPress.org.
		$api_url = "https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]={$theme_slug}";
		$response = wp_remote_get( $api_url, array( 'timeout' => 10 ) );

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( ! empty( $data ) ) {
				// Theme is on WordPress.org - good sign.
				$rating = $data['rating'] ?? 0;
				$num_ratings = $data['num_ratings'] ?? 0;

				if ( $num_ratings < 10 ) {
					$reputation_issues[] = 'Few user ratings (less than 10)';
				}

				if ( $rating < 70 ) {
					$reputation_issues[] = sprintf( 'Low rating (%.1f%%)', $rating );
				}

				$last_updated = isset( $data['last_updated'] ) ? strtotime( $data['last_updated'] ) : 0;
				if ( $last_updated && ( time() - $last_updated ) > ( 365 * DAY_IN_SECONDS ) ) {
					$reputation_issues[] = 'Not updated in over 1 year';
				}
			}
		} else {
			// Not on WordPress.org.
			$reputation_issues[] = 'Theme not from WordPress.org';
		}

		// Check author URI validity.
		if ( empty( $author_uri ) || ! filter_var( $author_uri, FILTER_VALIDATE_URL ) ) {
			$reputation_issues[] = 'No valid author URI provided';
		}

		if ( ! empty( $reputation_issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: theme name */
					__( 'Theme "%s" has reputation concerns. Review author credentials.', 'wpshadow' ),
					$current_theme->get( 'Name' )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/themes-author-reputation',
				'data'         => array(
					'theme_name' => $current_theme->get( 'Name' ),
					'author' => $author,
					'reputation_issues' => $reputation_issues,
				),
			);

			set_transient( $cache_key, $result, 7 * DAY_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 7 * DAY_IN_SECONDS );
		return null;
	}
}
