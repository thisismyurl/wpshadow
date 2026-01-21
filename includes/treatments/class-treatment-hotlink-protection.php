<?php
/**
 * Hotlink Protection Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment for adding basic hotlink protection rules to .htaccess.
 */
class Treatment_Hotlink_Protection extends Treatment_Base {
	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @return string
	 */
	public static function get_finding_id() {
		return 'hotlink-protection-missing';
	}
	
	/**
	 * Check if this treatment can be applied.
	 *
	 * @return bool True if treatment can run.
	 */
	public static function can_apply() {
		$htaccess = ABSPATH . '.htaccess';
		return file_exists( $htaccess ) && is_writable( $htaccess ) && ! self::has_existing_block();
	}
	
	/**
	 * Apply the treatment/fix.
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function apply() {
		$htaccess = ABSPATH . '.htaccess';
		if ( ! file_exists( $htaccess ) || ! is_writable( $htaccess ) ) {
			return array(
				'success' => false,
				'message' => '.htaccess is missing or not writable.',
			);
		}
		
		if ( self::has_existing_block() ) {
			return array(
				'success' => true,
				'message' => 'Hotlink protection is already enabled.',
			);
		}
		
		$host = wp_parse_url( get_option( 'home' ), PHP_URL_HOST );
		if ( empty( $host ) ) {
			return array(
				'success' => false,
				'message' => 'Could not determine site host for rule generation.',
			);
		}
		
		$rules = self::build_rules( $host );
		
		// Backup .htaccess
		copy( $htaccess, $htaccess . '.bak' );
		
		$contents = file_get_contents( $htaccess );
		$contents .= "\n" . $rules;
		
		if ( false === file_put_contents( $htaccess, $contents ) ) {
			return array(
				'success' => false,
				'message' => 'Failed to write hotlink protection rules.',
			);
		}
		
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		
		return array(
			'success' => true,
			'message' => 'Hotlink protection rules added to .htaccess.',
		);
	}
	
	/**
	 * Undo the treatment by removing the WPShadow hotlink block.
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function undo() {
		$htaccess = ABSPATH . '.htaccess';
		if ( ! file_exists( $htaccess ) || ! is_writable( $htaccess ) ) {
			return array(
				'success' => false,
				'message' => '.htaccess is missing or not writable.',
			);
		}
		
		$contents = file_get_contents( $htaccess );
		$updated  = preg_replace( '#\n?#?\s*# BEGIN WPShadow Hotlink Protection.*?# END WPShadow Hotlink Protection\s*#?\n?#is', '', $contents, -1, $replacements );
		
		if ( null === $updated ) {
			return array(
				'success' => false,
				'message' => 'Failed to parse .htaccess for removal.',
			);
		}
		
		if ( $replacements > 0 ) {
			file_put_contents( $htaccess, $updated );
			return array(
				'success' => true,
				'message' => 'Hotlink protection rules removed.',
			);
		}
		
		return array(
			'success' => false,
			'message' => 'No WPShadow hotlink block found to remove.',
		);
	}
	
	/**
	 * Build the hotlink protection rule block.
	 *
	 * @param string $host Site host.
	 * @return string
	 */
	private static function build_rules( $host ) {
		$escaped_host = preg_quote( $host, '/' );
		$extensions   = 'jpg|jpeg|png|gif|webp|svg';
		
		return <<<HTACCESS
# BEGIN WPShadow Hotlink Protection
RewriteEngine On
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^https?://([^.]+\.)?{$escaped_host} [NC]
RewriteCond %{HTTP_REFERER} !google\. [NC]
RewriteCond %{HTTP_REFERER} !bing\. [NC]
RewriteRule \.({$extensions})$ - [F,NC]
# END WPShadow Hotlink Protection
HTACCESS;
	}
	
	/**
	 * Check if the WPShadow hotlink block already exists.
	 *
	 * @return bool
	 */
	private static function has_existing_block() {
		$htaccess = ABSPATH . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			return false;
		}
		
		$contents = file_get_contents( $htaccess );
		return false !== strpos( $contents, '# BEGIN WPShadow Hotlink Protection' );
	}
}
