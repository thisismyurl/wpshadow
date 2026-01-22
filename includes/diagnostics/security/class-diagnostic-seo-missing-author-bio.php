<?php
declare(strict_types=1);
/**
 * Missing Author Bio Diagnostic
 *
 * Philosophy: SEO E-E-A-T - author authority matters
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing author bios (E-E-A-T signal).
 */
class Diagnostic_SEO_Missing_Author_Bio extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$users = get_users( array( 'who' => 'authors', 'number' => 5 ) );
		
		$missing_bio = 0;
		foreach ( $users as $user ) {
			if ( empty( $user->description ) ) {
				$missing_bio++;
			}
		}
		
		if ( $missing_bio > 0 ) {
			return array(
				'id'          => 'seo-missing-author-bio',
				'title'       => 'Authors Missing Bios',
				'description' => sprintf( '%d authors lack biographical information. E-E-A-T (Experience, Expertise, Authoritativeness, Trust) requires demonstrating author credentials. Add author bios with expertise.', $missing_bio ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-author-bios/',
				'training_link' => 'https://wpshadow.com/training/eeat-optimization/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
