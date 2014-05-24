
# change working directory
cd work

# build artifact packages
php build.php

# install composer-shared-installer plugin
./composer.sh global update

# install example package v1.0.0 to shared
./composer.sh require ngyuki/composer-shared-installer-example:1.0.0

# install example package v1.0.1 to shared
./composer.sh require ngyuki/composer-shared-installer-example:1.0.1
