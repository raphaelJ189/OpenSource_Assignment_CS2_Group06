#!/bin/sh
set -e

php /var/www/html/setup_db.php

exec apache2-foreground
