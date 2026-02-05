<?php
/**
 * NoSQL Injection Not Prevented Treatment
 *
 * Checks NoSQL injection prevention.
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
 * Treatment_No_SQL_Injection_Not_Prevented Class
 *
 * Performs treatment check for No Sql Injection Not Prevented.
 *
 * @since 1.6033.2033
 */
class Treatment_No_SQL_Injection_Not_Prevented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-sql-injection-not-prevented';

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
	protected static $description = 'Checks NoSQL injection prevention';

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
		if ( ! has_filter( 'init', 'validate_nosql_queries' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'NoSQL injection not prevented. Use schema validation and parameterized queries for all NoSQL operations.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/no-sql-injection-not-prevented',
				'context'       => array(
					'why'            => __( 'NoSQL injection exploits MongoDB/CouchDB/Redis queries. Similar to SQL injection but targets NoSQL databases. Attackers inject operators like $where, $regex to bypass authentication or extract data. OWASP A03: Injection includes NoSQL injection. Real scenario: Login query db.users.findOne({username: username_from_form}). Attacker sends {\"$ne\": null} as username → returns first user regardless of password. Cost: Database compromise, user account takeover, data exfiltration.', 'wpshadow' ),
					'recommendation' => __( '1. Never concatenate user input: WRONG - db.find({_id: user_id_from_input}). 2. Use schema validation: Mongoose schemas enforce field types. 3. Parameterize queries: db.collection.findOne({id: ObjectId(user_id)}). 4. Whitelist operators: Only allow specific query operators. 5. Avoid $where: Never use $where operator with user input. 6. Type casting: Convert input to expected type before query. 7. Input validation: Regex to ensure email format, ID is numeric, etc. 8. Principle of least privilege: Database user only has needed permissions. 9. Monitor NoSQL logs: Alert on unusual query patterns. 10. Security testing: Test with injection payloads ($ne, $regex, $where).', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'nosql-injection-prevention', 'nosql-injection-detection' );
			return $finding;
		}

		return null;
	}
}
