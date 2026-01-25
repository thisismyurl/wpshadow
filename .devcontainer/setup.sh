#!/bin/bash
set -e

echo "🚀 Setting up WordPress Plugin Development Environment..."

# Install system dependencies
apt-get update
apt-get install -y \
    zip \
    unzip \
    git \
    subversion \
    mariadb-client \
    wget

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-interaction --prefer-dist
else
    echo "⚠️  No composer.json found, creating one..."
    composer init --no-interaction --name=thisismyurl/wpshadow
fi

# Install PHP_CodeSniffer and WordPress Coding Standards
echo "📏 Installing WordPress Coding Standards..."
composer require --dev \
    squizlabs/php_codesniffer:^3.7 \
    wp-coding-standards/wpcs:^3.0 \
    phpcompatibility/phpcompatibility-wp:^2.1 \
    automattic/vipwpcs:^3.0 \
    phpstan/phpstan:^1.10 \
    --no-interaction

# Configure PHPCS
echo "⚙️  Configuring PHPCS..."
vendor/bin/phpcs --config-set installed_paths \
    vendor/wp-coding-standards/wpcs,vendor/phpcompatibility/phpcompatibility-wp,vendor/automattic/vipwpcs

vendor/bin/phpcs --config-set default_standard WordPress
vendor/bin/phpcs --config-set colors 1
vendor/bin/phpcs --config-set show_progress 1

# Install WP-CLI
echo "🔧 Installing WP-CLI..."
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

# Create phpcs.xml if it doesn't exist
if [ ! -f "phpcs.xml" ]; then
    echo "📄 Creating phpcs.xml..."
    cat > phpcs.xml <<'EOF'
<?xml version="1.0"?>
<ruleset name="WPShadow">
    <description>WordPress Coding Standards for WPShadow</description>

    <file>.</file>

    <exclude-pattern>*/node_modules/*</exclude-pattern>
    <exclude-pattern>*/build/*</exclude-pattern>
    <exclude-pattern>*/tests/*</exclude-pattern>
    <exclude-pattern>*/vendor/*</exclude-pattern>

    <rule ref="WordPress">
        <!-- Allow short array syntax -->
        <exclude name="Generic.Arrays.DisallowShortArraySyntax"/>
    </rule>

    <config name="testVersion" value="7.4-"/>
    <rule ref="PHPCompatibilityWP"/>

    <!-- Additional rules -->
    <rule ref="WordPress.WP.I18n">
        <properties>
            <property name="text_domain" type="array" value="wpshadow"/>
        </properties>
    </rule>

    <rule ref="WordPress.NamingConventions.PrefixAllGlobals">
        <properties>
            <property name="prefixes" type="array" value="wpshadow,WPShadow"/>
        </properties>
    </rule>
</ruleset>
EOF
fi

# Create phpunit.xml if it doesn't exist
if [ ! -f "phpunit.xml" ]; then
    echo "🧪 Creating phpunit.xml..."
    cat > phpunit.xml <<'EOF'
<?xml version="1.0"?>
<phpunit bootstrap="tests/bootstrap.php" backupGlobals="false" colors="true" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true">
    <testsuites>
        <testsuite name="WPShadow Test Suite">
            <directory>./tests/</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">./includes/</directory>
        </include>
    </coverage>
</phpunit>
EOF
fi

# Create .editorconfig if it doesn't exist
if [ ! -f ".editorconfig" ]; then
    echo "📝 Creating .editorconfig..."
    cat > .editorconfig <<'EOF'
# WordPress Coding Standards
# https://make.wordpress.org/core/handbook/best-practices/coding-standards/

root = true

[*]
charset = utf-8
end_of_line = lf
insert_final_newline = true
trim_trailing_whitespace = true
indent_style = tab
indent_size = 4

[*.{json,yml,yaml}]
indent_style = space
indent_size = 2

[*.md]
trim_trailing_whitespace = false
EOF
fi

# Install Node dependencies if package.json exists
if [ -f "package.json" ]; then
    echo "📦 Installing Node dependencies..."
    npm install
fi

echo "✅ Development environment setup complete!"
echo ""
echo "🎯 Quick Start:"
echo "  - WordPress: http://localhost:8080"
echo "  - phpMyAdmin: http://localhost:8081"
echo "  - Run PHPCS: composer run-script phpcs"
echo "  - Fix code: composer run-script phpcbf"
echo "  - Run tests: composer run-script test"
echo ""
