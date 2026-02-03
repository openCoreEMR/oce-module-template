#!/bin/bash
#
# Auto-registers the module in OpenEMR on first container boot.
#
# This script is run automatically when the Docker container starts,
# enabling the module without manual UI interaction.
#
# @package   OpenCoreEMR
# @copyright Copyright (c) 2026 OpenCoreEMR Inc
# @license   GNU General Public License 3

set -e

MODULE_NAME="{vendor-prefix}-module-{modulename}"
MODULE_DIR="/var/www/localhost/htdocs/openemr/interface/modules/custom_modules/${MODULE_NAME}"

# Database connection parameters (from environment)
DB_HOST="${MYSQL_HOST:-mysql}"
DB_USER="${MYSQL_USER:-openemr}"
DB_PASS="${MYSQL_PASS:-openemr}"
DB_NAME="${MYSQL_DATABASE:-openemr}"

echo "==> Checking module registration for ${MODULE_NAME}..."

# Wait for database to be ready
until mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1" "$DB_NAME" &>/dev/null; do
    echo "Waiting for database..."
    sleep 2
done

# Check if module is already registered
REGISTERED=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -N -e \
    "SELECT COUNT(*) FROM modules WHERE mod_directory = '${MODULE_NAME}'" "$DB_NAME" 2>/dev/null || echo "0")

if [ "$REGISTERED" = "0" ]; then
    echo "==> Registering module ${MODULE_NAME}..."

    # Get next mod_id
    NEXT_ID=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -N -e \
        "SELECT COALESCE(MAX(mod_id), 0) + 1 FROM modules" "$DB_NAME")

    # Register module
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_active, mod_ui_active, date)
VALUES (
    ${NEXT_ID},
    '${MODULE_NAME}',
    '${MODULE_NAME}',
    1,
    1,
    NOW()
);
EOF

    echo "==> Module registered successfully with mod_id ${NEXT_ID}"
else
    echo "==> Module already registered, skipping..."
fi

# Run module's install SQL if exists and not already run
INSTALL_SQL="${MODULE_DIR}/table.sql"
INSTALL_MARKER="${MODULE_DIR}/.docker-sql-installed"

if [ -f "$INSTALL_SQL" ] && [ ! -f "$INSTALL_MARKER" ]; then
    echo "==> Running module install SQL..."
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$INSTALL_SQL"
    touch "$INSTALL_MARKER"
    echo "==> Install SQL completed"
fi

echo "==> Module setup complete"
