#!/usr/bin/env bash
# Pre-commit guard for template-state hooks.
#
# Several local pre-commit hooks (php -l, composer-validate/normalize, phpcs,
# phpstan, rector, composer-require-checker) cannot run on the bare template
# because the placeholders ({ModuleName}, {vendorname}/...) make PHP files
# unparseable and composer.json fails composer's name regex. After bin/setup
# rewrites placeholders, the hooks should run normally.
#
# This script is a thin wrapper: it checks whether composer.json still
# contains a placeholder; if so, it skips the hook (exit 0). Otherwise it
# execs the wrapped command.

set -euo pipefail

readonly composer_json='composer.json'

main() {
    if [[ ! -f $composer_json ]]; then
        # Nothing to gate against; just run the hook.
        exec "$@"
    fi
    if grep -q '{vendorname}\|{vendor-prefix}\|{modulename}' "$composer_json"; then
        # shellcheck disable=SC2016 # %s is a printf spec, not a shell expansion
        printf 'precommit-template-guard: composer.json still contains template placeholders; skipping `%s`\n' "$1" >&2
        exit 0
    fi
    exec "$@"
}

main "$@"
