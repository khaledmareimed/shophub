#!/bin/bash
# =============================================================
# Database Setup Script
# Usage: chmod +x setup_db.sh && sudo bash setup_db.sh
# =============================================================

set -e

DB_NAME="khamar12_khaled"
DB_USER="khamar12_khaled"
DB_PASS="khaled2005"
MIGRATIONS_DIR="$(dirname "$0")/../database/migrations"

echo ">>> Creating database: $DB_NAME"
mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo ">>> Creating user: $DB_USER"
mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo ">>> Running migrations..."
for f in "$MIGRATIONS_DIR"/*.sql; do
    echo "  Running: $(basename $f)"
    mysql "$DB_NAME" < "$f"
done

echo ""
echo "✓ Done! Database '$DB_NAME' is ready."
echo ""
echo ">>> Tables created:"
mysql "$DB_NAME" -e "SHOW TABLES;"
