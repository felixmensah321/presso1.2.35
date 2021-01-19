#!/usr/bin/env sh


# Install Magento cron service
su --shell /bin/sh www-data --command "bin/magento cron:install --non-optional"

# Magento init
su --shell /bin/sh www-data --command "cp -rfv /mnt/shared-volume/env.php /var/www/html/app/etc"
su --shell /bin/sh www-data --command "bin/magento deploy:mode:set production --skip-compilation"
su --shell /bin/sh www-data --command "bin/magento setup:upgrade"
su --shell /bin/sh www-data --command "bin/magento setup:static-content:deploy en_US fr_FR fr_CH --jobs 4 --theme Magento/backend"
su --shell /bin/sh www-data --command "bin/magento app:config:import --no-interaction"
su --shell /bin/sh www-data --command "bin/magento cache:flush"

# Install Magento cron service
su --shell /bin/sh www-data --command "bin/magento cron:install"

# Execute services
cron && rsyslogd && apache2-foreground
