#!/usr/bin/env bash
set -euo pipefail

cd /workspaces/wpshadow

mkdir -p docs

# Collect existing KB article slugs from markdown files.
find dev-tools/kb-articles -type f -name '*.md' ! -name '_TEMPLATE.md' -print \
	| sed -E 's#.*/##' | sed -E 's#\.md$##' | sort -u > /tmp/kb_existing_slugs.txt

# Extract all KB URLs referenced by source code.
grep -RIno "https://wpshadow.com/kb/[^'\" ]*" includes --include='*.php' > /tmp/kb_refs.txt || true

# Build slug -> count table.
perl -ne '
	if (/^([^:]+):(\d+):(https:\/\/wpshadow\.com\/kb\/\S+)/) {
		$src = "$1:$2";
		$url = $3;
		$clean = $url;
		$clean =~ s/\?.*$//;
		$slug = $clean;
		$slug =~ s#^https://wpshadow\.com/kb/##;
		next if $slug eq q{};
		$count{$slug}++;
		$first_url{$slug} //= $clean;
		$first_src{$slug} //= $src;
	}
	END {
		for my $slug (sort keys %count) {
			print join(",", $slug, $first_url{$slug}, $count{$slug}, $first_src{$slug}) . "\n";
		}
	}
' /tmp/kb_refs.txt > /tmp/kb_slug_counts.csv

# Output missing slugs as a clean CSV.
out_file="docs/kb-articles-needed.csv"

perl -F, -lane '
	BEGIN {
		open my $existing_fh, "<", "/tmp/kb_existing_slugs.txt" or die $!;
		while (<$existing_fh>) {
			chomp;
			$existing{$_} = 1;
		}
		print "slug,example_url,reference_count,example_source,exists_in_repo,status";
	}

	$slug  = $F[0] // q{};
	$url   = $F[1] // q{};
	$count = $F[2] // q{};
	$src   = $F[3] // q{};

	for ($slug, $url, $count, $src) {
		s/\\n//g;
		s/[\r\n]//g;
		s/^\s+|\s+$//g;
	}

	next if $slug eq q{};
	next if exists $existing{$slug};

	for ($slug, $url, $count, $src) {
		s/"/""/g;
		$_ = q{"} . $_ . q{"};
	}

	print join(",", $slug, $url, $count, $src, q{"no"}, q{"to_write"});
' /tmp/kb_slug_counts.csv > "$out_file"

echo "Generated $out_file"
wc -l "$out_file"
