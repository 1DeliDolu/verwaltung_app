#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="${1:-.}"

create_dir() {
  mkdir -p "${ROOT_DIR%/}/$1"
}

create_file() {
  local path="${ROOT_DIR%/}/$1"
  mkdir -p "$(dirname "$path")"
  touch "$path"
}

dirs=(
  ".claude"
  ".claude/tasks"
  ".claude/agents"
  ".claude/skills/php-patterns"
  ".claude/skills/mvc-patterns"
  ".claude/skills/database-patterns"
  ".claude/skills/logging-patterns"
  ".claude/skills/testing-patterns"
  ".claude/skills/api-contract-review"
  ".claude/skills/clean-code"
)

files=(
  "README.md"
  "CLAUDE.md"
  ".claude/settings.local.json"
  ".claude/tasks/todo.md"
  ".claude/tasks/lessons.md"
  ".claude/agents/php-architect.md"
  ".claude/agents/php-backend-engineer.md"
  ".claude/agents/database-engineer.md"
  ".claude/agents/security-engineer.md"
  ".claude/agents/devops-engineer.md"
  ".claude/agents/code-reviewer.md"
  ".claude/agents/test-automator.md"
  ".claude/agents/performance-reviewer.md"
  ".claude/skills/php-patterns/SKILL.md"
  ".claude/skills/php-patterns/README.md"
  ".claude/skills/mvc-patterns/SKILL.md"
  ".claude/skills/mvc-patterns/README.md"
  ".claude/skills/database-patterns/SKILL.md"
  ".claude/skills/database-patterns/README.md"
  ".claude/skills/logging-patterns/SKILL.md"
  ".claude/skills/logging-patterns/README.md"
  ".claude/skills/testing-patterns/SKILL.md"
  ".claude/skills/testing-patterns/README.md"
  ".claude/skills/api-contract-review/SKILL.md"
  ".claude/skills/api-contract-review/README.md"
  ".claude/skills/clean-code/SKILL.md"
  ".claude/skills/clean-code/README.md"
)

for dir in "${dirs[@]}"; do
  create_dir "$dir"
done

for file in "${files[@]}"; do
  create_file "$file"
done