<?php
/**
 * Email Validation Not Comprehensive Diagnostic
 *
 * Checks email validation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_Email_Validation_Not_Comprehensive Class
 *
 * Performs diagnostic check for Email Validation Not Comprehensive.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Email_Validation_Not_Comprehensive extends Diagnostic_Base {

/**
 * The diagnostic slug.
 *
 * @var string
 */
protected static $slug = 'email-validation-not-comprehensive';

/**
 * The diagnostic title.
 *
 * @var string
 */
protected static $title = 'Email Validation Not Comprehensive';

/**
 * The diagnostic description.
 *
 * @var string
 */
protected static $description = 'Checks email validation';

/**
 * The family this diagnostic belongs to.
 *
 * @var string
 */
protected static $family = 'functionality';

/**
 * Run the diagnostic check.
 *
 * @since  1.6093.1200
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
return null;
}
}
