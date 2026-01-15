# GitHub Copilot Agent Setup

This directory contains GitHub Copilot Agent definitions for the WPSupport plugin ecosystem.

## Available Agents

### WPSupport Agent (`wpsupport-agent.md`)

The WPSupport Agent is configured to handle issues for:
- `plugin-wpshadow` (primary repository)
- Related module repositories (via shared knowledge)

## Using the Copilot Agent

### Method 1: Automatic Assignment (Recommended)

Use the GitHub CLI or API to assign Copilot to issues automatically:

```bash
# Assign Copilot to an issue
gh issue edit <issue_number> --add-assignee copilot
```

### Method 2: Manual Assignment via Web UI

1. Open the issue on GitHub
2. Click "Assignees" on the right sidebar
3. Search for "copilot" and select "Copilot"
4. Copilot will begin working on the issue

### Method 3: Direct Copilot Chat

Use GitHub Copilot in VS Code:

1. Open the repository in VS Code
2. Open Copilot Chat (Ctrl+Shift+Alt+I)
3. Reference the issue: `@workspace #123`
4. Reference the agent: `@workspace Use the wpsupport-agent.md profile`
5. Ask Copilot to solve the issue

## Agent Profiles

Each agent file contains:
- Core responsibilities and expertise areas
- Technology stack and tools information
- Code style and standards to follow
- Working procedures and best practices
- Common tasks and patterns
- Resources and documentation links

## Workflow

When Copilot is assigned to an issue:

1. **Analysis**: Copilot reads the issue and analyzes the codebase
2. **Planning**: Copilot creates a plan based on the agent profile
3. **Implementation**: Copilot writes code following the standards
4. **Testing**: Copilot creates tests and validates code
5. **PR Creation**: Copilot creates a pull request with the solution
6. **Review**: Team reviews and merges the PR

## Configuration

The agents are designed to work with:
- **PHP 8.0+** and **WordPress 6.0+**
- **Composer** for dependency management
- **PHPCS** for code standards
- **PHPStan** for static analysis
- **PHPUnit** for testing
- **GitHub Actions** for CI/CD

## Tips for Best Results

1. **Detailed Issues**: Include example code, error messages, and expected behavior
2. **Clear Scope**: Keep issues focused on specific functionality
3. **Context**: Link to related issues, PRs, or documentation
4. **Test Cases**: Provide test cases or reproduction steps
5. **Acceptance Criteria**: Define what "done" looks like

## Modifying Agents

To update agent profiles:

1. Edit the `.md` file in this directory
2. Ensure clarity and completeness
3. Test with Copilot to verify understanding
4. Commit with clear message explaining changes

## Support

For questions about the agent setup:
- Check the agent profile file for detailed instructions
- Review issue comments and PR discussions
- Refer to WordPress and PHP documentation
- Check the main README.md for project details

---

**Last Updated**: January 2026
