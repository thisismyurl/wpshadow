#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MANIFEST_FILE="$ROOT_DIR/scripts/wporg-release-manifest.txt"
BUILD_ROOT="$ROOT_DIR/build/wporg"
STAGE_DIR="$BUILD_ROOT/wpshadow"
ZIP_PATH=""
TARGET_VERSION=""
TESTED_UP_TO=""
REQUIRES_AT_LEAST=""
REQUIRES_PHP=""
SKIP_VALIDATION=0
SKIP_ZIP=0

usage() {
	cat <<'EOF'
Usage:
  bash scripts/release-wporg.sh --version <version> [options]

Options:
  --version <version>              Target plugin version. Required.
  --tested-up-to <version>         Update readme Tested up to header.
  --requires-at-least <version>    Update minimum WordPress version metadata.
  --requires-php <version>         Update minimum PHP version metadata.
  --skip-validation                Build stage package without lint/validation.
  --skip-zip                       Build stage package but do not create a zip.
  --help                           Show this help text.

Examples:
  bash scripts/release-wporg.sh --version 0.6093.1201 --tested-up-to 6.6
  bash scripts/release-wporg.sh --version 0.6093.1201 --tested-up-to 6.7 --requires-at-least 6.4 --requires-php 8.1
EOF
}

log() {
	printf '[release] %s\n' "$1"
}

fail() {
	printf '[release] ERROR: %s\n' "$1" >&2
	exit 1
}

require_file() {
	local path="$1"
	[[ -e "$path" ]] || fail "Required path not found: ${path#$ROOT_DIR/}"
}

update_metadata() {
	local today
	today="$(date '+%B %-d, %Y')"

	log "Updating release metadata to version $TARGET_VERSION"
	perl -0pi -e "s/^ \* Version: .*/ * Version: $TARGET_VERSION/m; s/^define\( 'WPSHADOW_VERSION', '[^']+' \);/define( 'WPSHADOW_VERSION', '$TARGET_VERSION' );/m;" "$ROOT_DIR/wpshadow.php"
	perl -0pi -e "s/^Stable tag: .*/Stable tag: $TARGET_VERSION/m;" "$ROOT_DIR/readme.txt"
	perl -0pi -e "s/^\*\*Version:\*\* .*/**Version:** $TARGET_VERSION (Format: 0.{last year digit}{julian day}.{hour}{minute} in Toronto time)/m; s/^\*\*Last Updated:\*\* .*/**Last Updated:** $today/m;" "$ROOT_DIR/README.md"
	sed -i -E "s/(\"version\"[[:space:]]*:[[:space:]]*\")[^\"]+(\")/\1$TARGET_VERSION\2/" "$ROOT_DIR/package.json"

	if [[ -n "$TESTED_UP_TO" ]]; then
		perl -0pi -e "s/^Tested up to: .*/Tested up to: $TESTED_UP_TO/m;" "$ROOT_DIR/readme.txt"
	fi

	if [[ -n "$REQUIRES_AT_LEAST" ]]; then
		perl -0pi -e "s/^ \* Requires at least: .*/ * Requires at least: $REQUIRES_AT_LEAST/m;" "$ROOT_DIR/wpshadow.php"
		perl -0pi -e "s/^Requires at least: .*/Requires at least: $REQUIRES_AT_LEAST/m;" "$ROOT_DIR/readme.txt"
	fi

	if [[ -n "$REQUIRES_PHP" ]]; then
		perl -0pi -e "s/^ \* Requires PHP: .*/ * Requires PHP: $REQUIRES_PHP/m;" "$ROOT_DIR/wpshadow.php"
		perl -0pi -e "s/^Requires PHP: .*/Requires PHP: $REQUIRES_PHP/m;" "$ROOT_DIR/readme.txt"
	fi
}

assert_version_sync() {
	local plugin_version
	local constant_version
	local stable_tag
	local package_version

	plugin_version="$(grep -m1 '^ \* Version:' "$ROOT_DIR/wpshadow.php" | sed 's/^ \* Version: //')"
	constant_version="$(grep -m1 "define( 'WPSHADOW_VERSION'" "$ROOT_DIR/wpshadow.php" | sed -E "s/.*'([0-9]+\.[0-9]+\.[0-9]+)'.*/\1/")"
	stable_tag="$(grep -m1 '^Stable tag:' "$ROOT_DIR/readme.txt" | sed 's/^Stable tag: //')"
	package_version="$(grep -m1 '"version"' "$ROOT_DIR/package.json" | sed -E 's/.*"version"\s*:\s*"([^"]+)".*/\1/')"

	[[ "$plugin_version" == "$TARGET_VERSION" ]] || fail "wpshadow.php plugin header version mismatch: $plugin_version"
	[[ "$constant_version" == "$TARGET_VERSION" ]] || fail "WPSHADOW_VERSION mismatch: $constant_version"
	[[ "$stable_tag" == "$TARGET_VERSION" ]] || fail "readme.txt stable tag mismatch: $stable_tag"
	[[ "$package_version" == "$TARGET_VERSION" ]] || fail "package.json version mismatch: $package_version"
}

prepare_stage() {
	log "Creating clean staging directory"
	rm -rf "$BUILD_ROOT"
	mkdir -p "$STAGE_DIR"

	while IFS= read -r entry || [[ -n "$entry" ]]; do
		[[ -z "$entry" || "$entry" =~ ^# ]] && continue
		require_file "$ROOT_DIR/$entry"
		if [[ -d "$ROOT_DIR/$entry" ]]; then
			mkdir -p "$STAGE_DIR/$entry"
			rsync -a "$ROOT_DIR/$entry/" "$STAGE_DIR/$entry/"
		else
			cp "$ROOT_DIR/$entry" "$STAGE_DIR/$entry"
		fi
	done < "$MANIFEST_FILE"
}

assert_stage_clean() {
	local extras
	extras="$(find "$STAGE_DIR" -mindepth 1 -maxdepth 1 -printf '%f\n' | sort)"
	log "Stage contents: $(printf '%s' "$extras" | tr '\n' ' ' | sed 's/ $//')"

	if find "$STAGE_DIR" -mindepth 1 -maxdepth 1 \( -name '.copilot' -o -name '.github' -o -name 'tests' -o -name 'dev-tools' -o -name 'scripts' -o -name 'docs' -o -name 'node_modules' -o -name 'vendor' -o -name 'dist' \) -print -quit | grep -q .; then
		fail 'Forbidden top-level development directory found in staged package'
	fi
}

check_future_since() {
	local report_file
	report_file="$BUILD_ROOT/future-since.txt"
	: > "$report_file"

	while IFS= read -r -d '' file; do
		TARGET_VERSION="$TARGET_VERSION" perl -ne '
			my $target = $ENV{TARGET_VERSION};
			if ( /\@since\s+([0-9]+\.[0-9]+\.[0-9]+)/ ) {
				my @left  = split /\./, $1;
				my @right = split /\./, $target;
				my $max = @left > @right ? scalar @left : scalar @right;
				for ( my $i = 0; $i < $max; $i++ ) {
					my $lv = $left[$i] // 0;
					my $rv = $right[$i] // 0;
					if ( $lv > $rv ) {
						print "$ARGV:$.:$1\n";
						last;
					}
					last if $lv < $rv;
				}
			}
		' "$file" >> "$report_file"
	done < <(find "$STAGE_DIR" -type f -name '*.php' -print0)

	if [[ -s "$report_file" ]]; then
		fail "Found @since tags beyond release target. See ${report_file#$ROOT_DIR/}"
	fi
}

lint_with_php() {
	local file="$1"
	php -l "$file" >/dev/null
}

lint_with_docker() {
	local host_file="$1"
	local relative_file
	relative_file="${host_file#$ROOT_DIR/}"
	docker compose exec -T wordpress php -l "/var/www/html/wp-content/plugins/wpshadow/$relative_file" >/dev/null < /dev/null
}

validate_php_syntax() {
	log "Validating PHP syntax in staged package"
	if command -v php >/dev/null 2>&1; then
		while IFS= read -r -d '' file; do
			if ! lint_with_php "$file"; then
				fail "PHP lint failed for ${file#$ROOT_DIR/}"
			fi
		done < <(find "$STAGE_DIR" -type f -name '*.php' -print0)
		return
	fi

	if command -v docker >/dev/null 2>&1 && [[ -f "$ROOT_DIR/docker-compose.yml" ]]; then
		while IFS= read -r -d '' file; do
			if ! lint_with_docker "$file"; then
				fail "PHP lint failed for ${file#$ROOT_DIR/}"
			fi
		done < <(find "$STAGE_DIR" -type f -name '*.php' -print0)
		return
	fi

	fail 'Neither local php nor docker compose validation is available'
}

validate_whitespace() {
	log "Checking staged package for whitespace hygiene"
	if find "$STAGE_DIR" -type f \( -name '*.php' -o -name '*.js' -o -name '*.css' -o -name '*.md' -o -name '*.txt' -o -name '*.json' -o -name '*.xml' -o -name '*.yml' -o -name '*.yaml' -o -name '*.sh' \) -print0 | xargs -0 grep -nE '[[:blank:]]+$' >/dev/null 2>&1; then
		fail 'Trailing whitespace found in staged package'
	fi
	if find "$STAGE_DIR" -type f \( -name '*.php' -o -name '*.js' -o -name '*.css' -o -name '*.md' -o -name '*.txt' -o -name '*.json' -o -name '*.xml' -o -name '*.yml' -o -name '*.yaml' -o -name '*.sh' \) -print0 | xargs -0 grep -nE '^\t+$' >/dev/null 2>&1; then
		fail 'Tab-only blank lines found in staged package'
	fi
	if find "$STAGE_DIR" -type f \( -name '*.php' -o -name '*.js' -o -name '*.css' -o -name '*.md' -o -name '*.txt' -o -name '*.json' -o -name '*.xml' -o -name '*.yml' -o -name '*.yaml' -o -name '*.sh' \) -print0 | xargs -0 grep -nU $'\r$' >/dev/null 2>&1; then
		fail 'CRLF line endings found in staged package'
	fi
}

create_zip() {
	ZIP_PATH="$BUILD_ROOT/wpshadow-$TARGET_VERSION-wporg.zip"
	log "Creating release zip at ${ZIP_PATH#$ROOT_DIR/}"
	(
		cd "$BUILD_ROOT"
		zip -qr "$(basename "$ZIP_PATH")" wpshadow
	)
}

while [[ $# -gt 0 ]]; do
	case "$1" in
		--version)
			TARGET_VERSION="$2"
			shift 2
			;;
		--tested-up-to)
			TESTED_UP_TO="$2"
			shift 2
			;;
		--requires-at-least)
			REQUIRES_AT_LEAST="$2"
			shift 2
			;;
		--requires-php)
			REQUIRES_PHP="$2"
			shift 2
			;;
		--skip-validation)
			SKIP_VALIDATION=1
			shift
			;;
		--skip-zip)
			SKIP_ZIP=1
			shift
			;;
		--help|-h)
			usage
			exit 0
			;;
		*)
			fail "Unknown option: $1"
			;;
	esac
done

[[ -n "$TARGET_VERSION" ]] || fail 'Missing required --version argument'
[[ -f "$MANIFEST_FILE" ]] || fail 'Release manifest file is missing'

log "Preparing WordPress.org release for version $TARGET_VERSION"
update_metadata
assert_version_sync
prepare_stage
assert_stage_clean

if [[ $SKIP_VALIDATION -eq 0 ]]; then
	check_future_since
	validate_php_syntax
	validate_whitespace
fi

if [[ $SKIP_ZIP -eq 0 ]]; then
	create_zip
fi

log "Release staging complete"
log "Stage directory: ${STAGE_DIR#$ROOT_DIR/}"

if [[ -n "$ZIP_PATH" ]]; then
	log "Release zip: ${ZIP_PATH#$ROOT_DIR/}"
fi