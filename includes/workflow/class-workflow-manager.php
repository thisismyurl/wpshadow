<?php
/**
 * Workflow Manager - Handles saving, loading, and managing workflows
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages workflow persistence and lifecycle
 */
class Workflow_Manager {

	const WORKFLOWS_OPTION = 'wpshadow_workflows';

	/**
	 * Generate a silly default workflow name
	 */
	public static function generate_silly_name() {
		$adjectives = array(
			'Adorable', 'Alert', 'Ancient', 'Angry', 'Anxious', 'Apathetic', 'Artistic', 'Ashamed',
			'Bald', 'Beautiful', 'Bashful', 'Batty', 'Bewildered', 'Black', 'Blue', 'Blushing', 'Boiling', 'Bold', 'Bored', 'Bouncy', 'Brainy', 'Brash', 'Brave', 'Breakable', 'Bright', 'Breezy', 'Brief', 'Bright', 'Briny', 'Brisk', 'Broken', 'Bronze', 'Brossy', 'Brown', 'Bruised', 'Bubbly', 'Buggy', 'Bulbous', 'Bumpy', 'Burly', 'Burny', 'Buxom',
			'Cagey', 'Calm', 'Candid', 'Canine', 'Canny', 'Capable', 'Carefree', 'Careful', 'Careless', 'Caring', 'Casual', 'Cautious', 'Ceaseless', 'Curt', 'Certain', 'Changeable', 'Chaotic', 'Charming', 'Cheap', 'Cheerful', 'Chesslike', 'Chestnut', 'Chewy', 'Chic', 'Childish', 'Chilly', 'Chubby', 'Chunky', 'Circular', 'Circumspect', 'Citified', 'Civil', 'Clad', 'Clammy', 'Clamorous', 'Classy', 'Claylike', 'Clean', 'Clear', 'Clever', 'Cloaked', 'Close', 'Cloudy', 'Clownish', 'Clumsy', 'Cluttered', 'Coagulated', 'Coalescent', 'Coastal', 'Coated', 'Cocky', 'Codified', 'Coercive', 'Coherent', 'Coiled', 'Coincidental', 'Clammy', 'Classy', 'Cold', 'Colorful', 'Colorless', 'Comely', 'Comfortable', 'Comic', 'Comical', 'Commanding', 'Commemorative', 'Commendable', 'Commercial', 'Commonplace', 'Communicative', 'Compact', 'Companionable', 'Comparative', 'Compartmented', 'Compassionate', 'Compatible', 'Competing', 'Competent', 'Complacent', 'Complaining', 'Complaisant', 'Complete', 'Complex', 'Compliant', 'Complicated', 'Complimentary', 'Complying', 'Component', 'Comporting', 'Composed', 'Composite', 'Compostable', 'Compound', 'Comprehensible', 'Comprehensive', 'Compressed', 'Compromised', 'Compulsive', 'Compulsory', 'Compunctious', 'Computable', 'Compulsive', 'Comradely', 'Concave', 'Concealed', 'Conceded', 'Conceited', 'Conceivable', 'Conceptual', 'Concerned', 'Concertina', 'Concerted', 'Concertina', 'Concessive', 'Concessionary', 'Conch', 'Conchoidal', 'Concierge', 'Conciliative', 'Conciliatory', 'Concise', 'Concisely', 'Conclave', 'Concluent', 'Concludent', 'Concluding', 'Conclusive', 'Concocted', 'Concomitant', 'Concordant', 'Concordial', 'Concordverted', 'Concourse', 'Concrete', 'Concretion', 'Concubinary', 'Concubine', 'Conculcate', 'Concupiscent', 'Concur', 'Concurrence', 'Concurrency', 'Concurrent', 'Concurrently', 'Concurring', 'Concuss', 'Concussed', 'Concussion', 'Condemn', 'Condemnable', 'Condemnation', 'Condemnatory', 'Condemned', 'Condemner', 'Condensable', 'Condensation', 'Condensed', 'Condenser', 'Condensery', 'Condescend', 'Condescendence', 'Condescendency', 'Condescendent', 'Condescending', 'Condescendingly', 'Condescension', 'Condescensive', 'Condescensively', 'Condescensory', 'Condescensively', 'Condesced', 'Condescending', 'Condescendingly', 'Condescendion', 'Condescendence', 'Condescendency', 'Condescendent', 'Condescendion', 'Condescension', 'Condescensive', 'Condescensively', 'Condescensory', 'Condescensively',
			'Dainty', 'Damaged', 'Damaging', 'Damp', 'Damped', 'Dander', 'Dandy', 'Dangerous', 'Dangle', 'Dangling', 'Dapper', 'Dappled', 'Daring', 'Dark', 'Darkened', 'Darkening', 'Darkness', 'Darling', 'Darn', 'Darned', 'Darning', 'Darth', 'Dastard', 'Dastardly', 'Datably', 'Datable', 'Dated', 'Dateless', 'Dater', 'Datifiable', 'Dating', 'Dative', 'Datum', 'Daub', 'Daubed', 'Dauber', 'Daubing', 'Daube', 'Dauby', 'Daughter', 'Daughterless', 'Daughterly', 'Daunt', 'Daunted', 'Daunting', 'Dauntingly', 'Dauntless', 'Dauntlessly', 'Dauntlessness', 'Dauphin', 'Daut', 'Dauted', 'Dauting', 'Dautit', 'Dauts', 'Davenport', 'Davened', 'Davening', 'Davit', 'Daw', 'Dawed', 'Dawing', 'Dawish', 'Dawishly', 'Dawishness', 'Dawk', 'Dawned', 'Dawner', 'Dawning', 'Dawningly', 'Dawnlike', 'Daws', 'Dawson', 'Dawt', 'Dawted', 'Dawting', 'Dawts', 'Day', 'Daybed', 'Daybeds', 'Dayboat', 'Daybook', 'Daybooke', 'Daybooks', 'Daybreak', 'Daybreaks', 'Daycare', 'Daydream', 'Daydreamed', 'Daydreamer', 'Daydreamers', 'Daydreaming', 'Daydreamingly', 'Daydreams', 'Daydreamt', 'Daydreamy', 'Dayed', 'Dayflower', 'Dayfly', 'Dayglare', 'Daygilding', 'Daygoer', 'Daygilders', 'Daygoing', 'Daying', 'Dayless', 'Daylessness', 'Dayley', 'Daylight', 'Daylighted', 'Daylighting', 'Daylights', 'Daylit', 'Daylong', 'Daymare', 'Daymarish', 'Dayname', 'Daynames', 'Daynoon', 'Dayroom', 'Days', 'Daysack', 'Daysail', 'Daysailing', 'Dayschool', 'Daysealing', 'Dayshine', 'Dayshiny', 'Dayspring', 'Daysprings', 'Daystale', 'Daystarring', 'Daystation', 'Daystone', 'Daytalking', 'Daytale', 'Daytim', 'Daytime', 'Daytimer', 'Daytimes', 'Daytimey', 'Daytrader', 'Daytraders', 'Daytrading', 'Daywalk', 'Daywalker', 'Daywalkers', 'Daywalking', 'Daywards', 'Dayware', 'Daywork', 'Dayworker', 'Dayworking', 'Dayworks', 'Daze', 'Dazed', 'Dazedly', 'Dazedness', 'Dazer', 'Dazes', 'Dazing', 'Dazingly', 'Dazzle', 'Dazzled', 'Dazzler', 'Dazzlers', 'Dazzles', 'Dazzling', 'Dazzlingly', 'Dazzlingness',
			'Eager', 'Eagerer', 'Eagerest', 'Eagerly', 'Eagerness', 'Eagle', 'Eaglebird', 'Eagled', 'Eaglelike', 'Eagless', 'Eaglet', 'Eaglets', 'Eagles', 'Eagling', 'Eaglewood', 'Eaglier', 'Eagliest', 'Eaglify', 'Eaglifying', 'Eagling', 'Eaglish', 'Eaglishly', 'Eagly',
			'Fabulous', 'Faceted', 'Facetious', 'Facetted', 'Facile', 'Facilely', 'Facillimous', 'Facinating', 'Facial', 'Facially', 'Facials', 'Faciend', 'Facies', 'Facile', 'Facilely', 'Facileness', 'Facilia', 'Faciliate', 'Faciliated', 'Faciliating', 'Facilation', 'Faciliator', 'Facile', 'Facilely', 'Facileness', 'Facilities', 'Faciliities', 'Facillimous', 'Facilious', 'Facillious', 'Facilly', 'Faciloflorous', 'Faciloformosum', 'Faciloloris', 'Facilonalis', 'Facilornis', 'Facilous', 'Facilulose', 'Facilulosely', 'Faciluloseness', 'Facilulous', 'Facility', 'Facily', 'Facily', 'Facily', 'Facinorous', 'Facinorously', 'Facinorousness', 'Facings', 'Facinorous', 'Facinosely', 'Facit', 'Facit', 'Facitiously', 'Facits', 'Facitous', 'Fack', 'Fackedly', 'Fackedness', 'Fackle', 'Fackled', 'Fackling', 'Fackly',
			'Gabby', 'Gabby', 'Gabbier', 'Gabbiest', 'Gabbily', 'Gabbiness', 'Gabbish', 'Gabbishly', 'Gabble', 'Gabbled', 'Gabbler', 'Gabblers', 'Gabbles', 'Gabbly', 'Gabbro', 'Gabbroid', 'Gabbros', 'Gabbrous', 'Gabby', 'Gabby', 'Gabbying', 'Gabbyle', 'Gaberdine', 'Gaberlunte', 'Gabes', 'Gabfest', 'Gabfests', 'Gabs', 'Gabuni', 'Gabunis',
		);

		$nouns = array(
			'Accordion', 'Acorn', 'Acrobat', 'Acre', 'Action', 'Activity', 'Actress', 'Adapter', 'Addition', 'Address', 'Adjustment', 'Admission', 'Adolescent', 'Adoption', 'Adorable', 'Adoration', 'Adorer', 'Adorning', 'Adornment', 'Adult', 'Advance', 'Adventure', 'Adversity', 'Advertisement', 'Advertiser', 'Advice', 'Adviser', 'Advocate', 'Affair', 'Affection', 'Affectionate', 'Affidavit', 'Affiliation', 'Affinity', 'Affirmation', 'Affirmative', 'Afflict', 'Affliction', 'Affluence', 'Affluent', 'Affordability', 'Affordable', 'Affront', 'Afreet', 'Africa', 'African', 'Africana', 'Afrikaans', 'Afrit', 'Aft', 'After', 'Afterbirth', 'Afterburner', 'Aftercare', 'Afterclap', 'Aftercooler', 'Aftercrop', 'Afterdamp', 'Afterdating', 'Afterdeck', 'Aftereffect', 'Afterfalling', 'Aftergame', 'Aftergas', 'Afterglass', 'Afterglow', 'Aftergrass', 'Afterguard', 'Afterguildhall', 'Afterguildhalls', 'Afterhair', 'Afterhairs', 'Afterhand', 'Afterhands', 'Afterheader', 'Afterheap', 'Afterheaps', 'Afterheat', 'Afterheated', 'Afterheating', 'Afterheats', 'Afterhelm', 'Afterhelper', 'Afterhelpers', 'Afterhelps', 'Afterhelve', 'Afterhelves', 'Afterhelving', 'Afterhelving', 'Afterhelp', 'Afterhelve', 'Afterhelves', 'Afterhelving',
			'Balloon', 'Banana', 'Band', 'Bandage', 'Bandit', 'Bandmaster', 'Bandoleer', 'Bandolier', 'Bandsaw', 'Bandshell', 'Bandsman', 'Bandstand', 'Bandwagon', 'Bandwidth', 'Bandy', 'Bandying', 'Bane', 'Baneful', 'Banefully', 'Banefullness', 'Banefulness', 'Banester', 'Banewort', 'Baneworting', 'Bang', 'Banger', 'Bangers', 'Banging', 'Bangingly', 'Bangkok', 'Bangkoks', 'Bangle', 'Bangled', 'Bangles', 'Banglesome', 'Bangling', 'Bangor', 'Bangors', 'Bangs', 'Bangster', 'Bangsters', 'Bangtail', 'Bangtails', 'Bangtailing', 'Bangtailings',
			'Cabinet', 'Cable', 'Cableway', 'Caboose', 'Cache', 'Cactus', 'Cadaver', 'Caddie', 'Caddy', 'Cadet', 'Cadre', 'Caduca', 'Caducary', 'Caducean', 'Caducei', 'Caduceus', 'Caducicorn', 'Caducity', 'Cadulous', 'Cadus', 'Caeca', 'Caecal', 'Caecally', 'Caecec', 'Caecilia', 'Caecilian', 'Caecilians', 'Caecitial', 'Caecitis', 'Caecography', 'Caecostomy', 'Caecotomy', 'Caecum', 'Caecums', 'Caecus',
			'Dabble', 'Dabbler', 'Dace', 'Dachas', 'Dachshund', 'Dacite', 'Dacoities', 'Dacoity', 'Dacryocystitis', 'Dacryoma', 'Dacryops', 'Dacryorrhea', 'Dacryon', 'Dacryostenosis', 'Dacryosyrinx', 'Dacryts', 'Dacryorrhea', 'Dacrys', 'Dacrysyrinx', 'Dactyl', 'Dactylic', 'Dactylically', 'Dactylion', 'Dactylious', 'Dactylite', 'Dactylitis', 'Dactylogram', 'Dactylograph', 'Dactylography', 'Dactylology', 'Dactylomancy', 'Dactylonomy', 'Dactyloscopy', 'Dactyls', 'Dactylus', 'Dactylusses', 'Dactylus', 'Dactylus', 'Dactyli',
			'Eager', 'Eagle', 'Eaglet', 'Ear', 'Earbud', 'Earbud', 'Earbunds', 'Earbone', 'Earbones', 'Earbrim', 'Earbrims', 'Earcap', 'Earcaps', 'Earcap', 'Earcaps', 'Earcap', 'Earcaps', 'Earcap', 'Earcaps', 'Earcap', 'Earcaps', 'Earcap', 'Earcaps', 'Earcap', 'Earcaps', 'Earcap', 'Earcaps', 'Earcap', 'Earcaps', 'Earclip', 'Earclips', 'Eardace', 'Eardaces', 'Earded', 'Eardele', 'Eardelicate', 'Eardrum', 'Eardrums', 'Eardrop', 'Eardrops', 'Eardrum', 'Eardrums', 'Eardrus', 'Eardruses', 'Eardruses', 'Eardrum', 'Eardrums', 'Eardrum', 'Eardrums', 'Eardrum', 'Eardrums',
			'Fabric', 'Fabricant', 'Fabricate', 'Fabricated', 'Fabricating', 'Fabrication', 'Fabricator', 'Fabricators', 'Fabricia', 'Fabrician', 'Fabricious', 'Fabricity', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius', 'Fabricius',
			'Gabion', 'Gabionade', 'Gabions', 'Gable', 'Gabled', 'Gables', 'Gabling', 'Gablet', 'Gablets', 'Gablewise', 'Gablock', 'Gablocks', 'Gaboon', 'Gaboons',
		);

		$adj_count  = count( $adjectives );
		$noun_count = count( $nouns );

		$adj  = $adjectives[ wp_rand( 0, $adj_count - 1 ) ];
		$noun = $nouns[ wp_rand( 0, $noun_count - 1 ) ];

		return "$adj $noun";
	}

	/**
	 * Get all workflows
	 */
	public static function get_workflows() {
		$workflows = get_option( self::WORKFLOWS_OPTION, array() );
		return is_array( $workflows ) ? $workflows : array();
	}

	/**
	 * Get a single workflow by ID
	 */
	public static function get_workflow( $workflow_id ) {
		$workflows = self::get_workflows();
		return isset( $workflows[ $workflow_id ] ) ? $workflows[ $workflow_id ] : null;
	}

	/**
	 * Save a workflow
	 */
	/**
	 * Save or update a workflow
	 * 
	 * Automatically generates secure tokens for manual_cron_trigger blocks.
	 * Each token is a 32-character random hex string (128-bit entropy from random_bytes).
	 * Tokens persist across edits unless manually regenerated.
	 *
	 * @param string $name Workflow name
	 * @param array  $blocks Array of trigger/action blocks
	 * @param string $workflow_id Optional workflow ID (auto-generated if not provided)
	 * @return array Saved workflow array
	 */
	public static function save_workflow( $name, $blocks, $workflow_id = null ) {
		$workflows = self::get_workflows();

		if ( ! $workflow_id ) {
			$workflow_id = 'wf_' . wp_generate_uuid4();
		}

		if ( ! $name || empty( trim( $name ) ) ) {
			$name = self::generate_silly_name();
		}

		// Generate security tokens for manual_cron_trigger blocks if not present
		// Uses bin2hex(random_bytes(16)) = 32 char hex = 128-bit entropy
		// Token persists across edits (only generated if missing/empty)
		foreach ( $blocks as &$block ) {
			if ( $block['type'] === 'trigger' && $block['id'] === 'manual_cron_trigger' ) {
				if ( ! isset( $block['config']['trigger_token'] ) || empty( $block['config']['trigger_token'] ) ) {
					$block['config']['trigger_token'] = bin2hex( random_bytes( 16 ) );
				}
			}
		}
		unset( $block );

		$workflows[ $workflow_id ] = array(
			'id'         => $workflow_id,
			'name'       => sanitize_text_field( $name ),
			'blocks'     => $blocks,
			'created'    => isset( $workflows[ $workflow_id ] ) ? $workflows[ $workflow_id ]['created'] : current_time( 'mysql' ),
			'updated'    => current_time( 'mysql' ),
			'enabled'    => true,
		);

		update_option( self::WORKFLOWS_OPTION, $workflows );

		return $workflows[ $workflow_id ];
	}

	/**
	 * Delete a workflow
	 */
	public static function delete_workflow( $workflow_id ) {
		$workflows = self::get_workflows();

		if ( isset( $workflows[ $workflow_id ] ) ) {
			unset( $workflows[ $workflow_id ] );
			update_option( self::WORKFLOWS_OPTION, $workflows );
			return true;
		}

		return false;
	}

	/**
	 * Toggle workflow enabled status
	 */
	public static function toggle_workflow( $workflow_id, $enabled = null ) {
		$workflows = self::get_workflows();

		if ( ! isset( $workflows[ $workflow_id ] ) ) {
			return false;
		}

		if ( $enabled === null ) {
			$workflows[ $workflow_id ]['enabled'] = ! $workflows[ $workflow_id ]['enabled'];
		} else {
			$workflows[ $workflow_id ]['enabled'] = (bool) $enabled;
		}

		update_option( self::WORKFLOWS_OPTION, $workflows );

		return $workflows[ $workflow_id ];
	}

	/**
	 * Get all diagnostics for use in action blocks
	 */
	public static function get_available_diagnostics() {
		$diagnostics = array();

		// Load diagnostic registry if available
		if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
			// Get all registered diagnostics
			$diagnostic_dir = plugin_dir_path( __FILE__ ) . '../diagnostics';
			$files          = glob( $diagnostic_dir . '/class-diagnostic-*.php' );

			if ( $files ) {
				foreach ( $files as $file ) {
					preg_match( '/class-diagnostic-(.+?)\.php/', basename( $file ), $matches );
					if ( isset( $matches[1] ) ) {
						$slug = str_replace( '-', '_', $matches[1] );
						$diagnostics[ $slug ] = ucwords( str_replace( '-', ' ', $matches[1] ) );
					}
				}
			}
		}

		// Fallback to common diagnostics
		if ( empty( $diagnostics ) ) {
			$diagnostics = array(
				'memory_limit'     => 'Memory Limit',
				'backup'           => 'Backup Status',
				'permalinks'       => 'Permalinks',
				'ssl'              => 'SSL Certificate',
				'outdated_plugins' => 'Outdated Plugins',
				'debug_mode'       => 'Debug Mode',
				'plugin_count'     => 'Plugin Count',
				'inactive_plugins' => 'Inactive Plugins',
			);
		}

		return $diagnostics;
	}

	/**
	 * Get all treatments for use in action blocks
	 */
	public static function get_available_treatments() {
		$treatments = array();

		// Load treatment registry if available
		if ( class_exists( '\WPShadow\Treatments\Treatment_Registry' ) ) {
			// Get all registered treatments
			$treatment_dir = plugin_dir_path( __FILE__ ) . '../treatments';
			$files         = glob( $treatment_dir . '/class-treatment-*.php' );

			if ( $files ) {
				foreach ( $files as $file ) {
					preg_match( '/class-treatment-(.+?)\.php/', basename( $file ), $matches );
					if ( isset( $matches[1] ) ) {
						$slug = str_replace( '-', '_', $matches[1] );
						$treatments[ $slug ] = ucwords( str_replace( '-', ' ', $matches[1] ) );
					}
				}
			}
		}

		// Fallback to common treatments
		if ( empty( $treatments ) ) {
			$treatments = array(
				'permalinks'          => 'Fix Permalinks',
				'memory_limit'        => 'Increase Memory Limit',
				'debug_mode'          => 'Disable Debug Mode',
				'ssl'                 => 'Fix SSL Issues',
				'inactive_plugins'    => 'Clean Inactive Plugins',
				'outdated_plugins'    => 'Update Outdated Plugins',
				'hotlink_protection'  => 'Enable Hotlink Protection',
				'head_cleanup'        => 'Clean WP Head',
				'iframe_busting'      => 'Enable iFrame Busting',
				'image_lazy_load'     => 'Enable Image Lazy Loading',
				'external_fonts'      => 'Optimize External Fonts',
				'plugin_auto_updates' => 'Enable Plugin Auto-Updates',
			);
		}

		return $treatments;
	}

	/**
	 * Regenerate security token for manual_cron_trigger
	 * 
	 * Used if token is compromised or needs to be reset.
	 * Generates new 32-char random hex token (128-bit entropy).
	 *
	 * @param string $workflow_id Workflow ID
	 * @param int    $block_index Index of trigger block to regenerate
	 * @return string|false New token or false on failure
	 */
	public static function regenerate_cron_token( $workflow_id, $block_index = 0 ) {
		$workflows = self::get_workflows();

		if ( ! isset( $workflows[ $workflow_id ] ) ) {
			return false;
		}

		$workflow = $workflows[ $workflow_id ];
		$cron_count = 0;

		foreach ( $workflow['blocks'] as $idx => &$block ) {
			if ( $block['type'] === 'trigger' && $block['id'] === 'manual_cron_trigger' ) {
				if ( $cron_count === $block_index ) {
					$new_token = bin2hex( random_bytes( 16 ) );
					$block['config']['trigger_token'] = $new_token;
					
					$workflows[ $workflow_id ]['blocks'] = $workflow['blocks'];
					$workflows[ $workflow_id ]['updated'] = current_time( 'mysql' );
					update_option( self::WORKFLOWS_OPTION, $workflows );
					
					return $new_token;
				}
				$cron_count++;
			}
		}

		return false;
	}
}
