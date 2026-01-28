#!/bin/bash
# Fetch issues 1726-1800 and categorize them

echo "Fetching issues 1726-1800..."
echo "Issue #,Title,Labels" > issues_1726_1800.csv

for i in {1726..1800}; do
    echo "Fetching issue #$i..." >&2
    # Using gh CLI if available, otherwise skip
    if command -v gh &> /dev/null; then
        gh issue view $i --json number,title,labels 2>/dev/null | \
        jq -r '"\(.number),\"\(.title)\",\(.labels | map(.name) | join(";"))"' >> issues_1726_1800.csv 2>/dev/null || \
        echo "$i,NOT_FOUND," >> issues_1726_1800.csv
    else
        echo "$i,NEED_GH_CLI," >> issues_1726_1800.csv
    fi
    sleep 0.1  # Rate limiting
done

echo "Summary:"
wc -l issues_1726_1800.csv
