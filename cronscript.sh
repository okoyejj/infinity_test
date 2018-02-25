#!/bin/sh
crontab -l
crontab -e 
#not display in the console
#Run every minute
* * * * * /usr/bin/php /Applications/MAMP/htdocs/php_daemon/cron.php >/dev/null$
