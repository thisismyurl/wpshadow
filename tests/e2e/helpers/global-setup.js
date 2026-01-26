/**
 * Global Setup for Playwright E2E Tests
 * Runs once before all tests
 * 
 * @package WPShadow\Tests\E2E
 */

module.exports = async () => {
	console.log('🚀 Setting up E2E test environment...');
	
	// WordPress connection check
	const WP_BASE_URL = process.env.WP_BASE_URL || 'http://localhost:9000';
	
	try {
		const response = await fetch(`${WP_BASE_URL}/wp-admin/`);
		if (response.ok) {
			console.log('✅ WordPress site is accessible');
		} else {
			console.warn(`⚠️  WordPress returned status ${response.status}`);
		}
	} catch (error) {
		console.error('❌ Cannot reach WordPress site:', error.message);
		console.log('\n💡 Make sure WordPress is running:');
		console.log('   - Start Docker: cd dev-tools && docker-compose up');
		console.log('   - Or set WP_BASE_URL environment variable to your WordPress URL');
		console.log('\n⏭️  Continuing with tests (they will fail if WordPress is not available)...\n');
	}
};
