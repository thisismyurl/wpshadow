<?php
/**
 * NoSQL Injection Not Prevented Treatment
 *
 * Checks NoSQL injection.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_NoSQL_Injection_Not_Prevented Class
 *
 * Performs treatment check for Nosql Injection Not Prevented.
 *
 * @since 1.6033.2033
 */
class Treatment_NoSQL_Injection_Not_Prevented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'nosql-injection-not-prevented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'NoSQL Injection Not Prevented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks NoSQL injection';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'sanitize_nosql_queries' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'NoSQL injection not prevented. Parameterize all NoSQL queries and validate/sanitize query operators like $where and $regex.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/nosql-injection-not-prevented',
				'context'       => array(
					'why'            => __( 'NoSQL injection attacks exploit MongoDB/CouchDB/Redis query syntax. Attackers inject operators like $ne, $regex, $where to bypass security. OWASP A03 includes NoSQL injection. Real scenario: Login finds user: db.users.findOne({email: email_from_form}). Attacker sends {\"$ne\": null} → bypasses authentication. MongoDB injection more common as WordPress adoption increases (WP + Node.js stacks). Cost: Database compromise, privilege escalation, full data breach.', 'wpshadow' ),
					'recommendation' => __( '1. Always use parameterization: db.find({_id: ObjectId(id)}). 2. Avoid $where operator completely: Never use with user input. 3. Validate input types: Ensure ID is ObjectId, email is string. 4. Whitelist fields: Only allow queries on specific fields. 5. Reject dangerous operators: Block $ne, $regex, $where if from user. 6. Use schema validation: Mongoose/schema libraries enforce types. 7. Escape special characters: Process user input before query building. 8. Log NoSQL queries: Monitor for suspicious patterns. 9. Test with payloads: Try $ne, $regex injection in dev. 10. Least privilege: Database user has minimal permissions.', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'nosql-injection-prevention', 'nosql-injection-detection' );
			return $finding;
		}

		return null;
	}
}
