#! /bin/bash
## 
# This restarts the Intranet Wordpress sites found under docker-wordpress.
#  It doesn't have to be set to a cron task if docker-compose is used.
#  docker-compose will restart these services automatically if needed.
##

cd /home/phin/intranet/wordpress-docker-compose
docker-compose restart >> /home/phin/intranet/logs/cron.wp.log
