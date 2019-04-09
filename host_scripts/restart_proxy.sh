#! /bin/bash

##
# Restart all open proxy services to release traffic. This is useful as a crontab task. 
# Proxy connections can be reestablished once the services are restarted
##

cd /home/phin/intranet/docker-squid
sudo docker-compose restart >> /home/phin/intranet/logs/crontab.squid.log
