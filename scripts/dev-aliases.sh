#!/bin/bash
# WPShadow Development Aliases - Source this in your shell
# Add to ~/.bashrc: source /workspaces/wpshadow/scripts/dev-aliases.sh

# === Quick Navigation ===
alias ws='cd /workspaces/wpshadow'
alias wsinc='cd /workspaces/wpshadow/includes'
alias wsdiag='cd /workspaces/wpshadow/includes/diagnostics'
alias wstreat='cd /workspaces/wpshadow/includes/treatments'
alias wsdocs='cd /workspaces/wpshadow/docs'

# === Git Shortcuts ===
alias gsync='/workspaces/wpshadow/scripts/git-sync-check.sh'
alias gcommit='/workspaces/wpshadow/scripts/git-auto-commit.sh'
alias gstatus='git status --short'
alias glog='git log --oneline --graph --decorate -10'
alias gpull='git pull origin main'
alias gpush='git push origin main'
alias gdiff='git diff'
alias gshow='git show'

# === Testing ===
alias phpcheck='find /workspaces/wpshadow/includes -name "*.php" -not -path "*/vendor/*" -not -path "*/documented/*" -exec php -l {} \; | grep -v "No syntax errors"'
alias phpcs='cd /workspaces/wpshadow && composer phpcs'
alias phpstan='cd /workspaces/wpshadow && composer phpstan'
alias testall='cd /workspaces/wpshadow && composer phpcs && composer phpstan'

# === Docker Management ===
alias dps='docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"'
alias dup='cd /workspaces/wpshadow && docker-compose up -d'
alias ddown='cd /workspaces/wpshadow && docker-compose down'
alias drestart='docker restart wpshadow-test && sleep 3'
alias dlogs='docker logs -f wpshadow-test'
alias dexec='docker exec -it wpshadow-test bash'

# === WordPress CLI (in Docker) ===
alias wp='docker exec wpshadow-test wp --allow-root'
alias wpcache='docker exec wpshadow-test wp cache flush --allow-root && echo "✅ Cache flushed"'
alias wpplugins='docker exec wpshadow-test wp plugin list --allow-root'
alias wpdb='docker exec wpshadow-test wp db'

# === Development Helpers ===
alias guardian='/workspaces/wpshadow/scripts/guardian-check.sh'
alias quicktest='/workspaces/wpshadow/scripts/test-wp-load.sh'
alias watchlogs='docker exec -it wpshadow-test tail -f /var/www/html/wp-content/debug.log'
alias auto-continue='tail -f /tmp/auto-continue-watcher.log'
alias clearlogs='docker exec wpshadow-test bash -c "echo \"\" > /var/www/html/wp-content/debug.log" && echo "✅ Debug log cleared"'

# === Helpful Functions ===
# Create a new feature branch
newbranch() {
    if [ -z "$1" ]; then
        echo "Usage: newbranch <branch-name>"
        return 1
    fi
    git checkout -b "feature/$1"
    echo "✅ Created and switched to: feature/$1"
}

# Quick commit with conventional commit format
feat() {
    if [ -z "$1" ]; then
        echo "Usage: feat \"description\""
        return 1
    fi
    /workspaces/wpshadow/scripts/git-auto-commit.sh "feat: $1"
}

fix() {
    if [ -z "$1" ]; then
        echo "Usage: fix \"description\""
        return 1
    fi
    /workspaces/wpshadow/scripts/git-auto-commit.sh "fix: $1"
}

refactor() {
    if [ -z "$1" ]; then
        echo "Usage: refactor \"description\""
        return 1
    fi
    /workspaces/wpshadow/scripts/git-auto-commit.sh "refactor: $1"
}

docs() {
    if [ -z "$1" ]; then
        echo "Usage: docs \"description\""
        return 1
    fi
    /workspaces/wpshadow/scripts/git-auto-commit.sh "docs: $1"
}

# Find a diagnostic or treatment class
findclass() {
    if [ -z "$1" ]; then
        echo "Usage: findclass <pattern>"
        return 1
    fi
    find /workspaces/wpshadow/includes -name "*$1*.php" -not -path "*/vendor/*" -not -path "*/documented/*"
}

# Search for a pattern in PHP files
findcode() {
    if [ -z "$1" ]; then
        echo "Usage: findcode <pattern>"
        return 1
    fi
    grep -r "$1" /workspaces/wpshadow/includes --include="*.php" --exclude-dir=vendor --exclude-dir=documented
}

# Show philosophy docs
philosophy() {
    cat /workspaces/wpshadow/docs/PRODUCT_PHILOSOPHY.md | less
}

# Show roadmap
roadmap() {
    cat /workspaces/wpshadow/docs/ROADMAP.md | less
}

# Show technical status
status() {
    cat /workspaces/wpshadow/docs/TECHNICAL_STATUS.md | less
}

echo "✅ WPShadow development aliases loaded!"
echo ""
echo "Quick commands:"
echo "  ws          - Go to workspace root"
echo "  gsync       - Check git sync"
echo "  gcommit     - Quick commit+push"
echo "  guardian    - Full environment check"
echo "  phpcs       - Run coding standards check"
echo "  quicktest   - Test WordPress load"
echo "  dexec       - SSH into Docker container"
echo "  wp          - WordPress CLI (in Docker)"
echo ""
echo "Helper functions:"
echo "  newbranch <name>  - Create feature branch"
echo "  feat \"msg\"        - Commit with feat: prefix"
echo "  fix \"msg\"         - Commit with fix: prefix"
echo "  findclass <name>  - Find diagnostic/treatment class"
echo "  philosophy        - View product philosophy"
echo ""
