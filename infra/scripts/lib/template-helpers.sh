#!/usr/bin/env bash

template_escape_sed_replacement() {
  local value="$1"

  value="${value//\\/\\\\}"
  value="${value//&/\\&}"
  value="${value//#/\\#}"

  printf '%s' "${value}"
}

template_assert_no_unresolved_placeholders() {
  local path="$1"

  if grep -Eq "__[A-Z_]+__" "${path}"; then
    echo "Unresolved placeholder found in ${path}" >&2
    return 1
  fi
}
