#!/bin/bash
set -e

APP_NAME="$1"
APP_PORT="$2"
APP_DIR="/var/www/$APP_NAME"

if [ -z "$APP_NAME" ]; then
  echo "Usage: deploy_app.sh <app_name>"
  exit 1
fi

echo "Deploying $APP_NAME ..."

# Prepare directory
mkdir -p "$APP_DIR"

# Generate Apache config automatically
cat <<EOF >/etc/apache2/sites-available/$APP_NAME.conf
<VirtualHost *:${APP_PORT}>
    DocumentRoot $APP_DIR

    <Directory $APP_DIR>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/apache2/${APP_NAME}_error.log
    CustomLog /var/log/apache2/${APP_NAME}_access.log combined
</VirtualHost>
EOF

# Enable site
a2ensite $APP_NAME.conf

# Reload apache
systemctl reload apache2

echo "Deployment completed for $APP_NAME"