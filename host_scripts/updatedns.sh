#! /bin/bash

##
# This calls the notracking servers to update config/hostnames.txt and domains.txt.
# Then, it restarts the main Intranet services found in ./docker-compose.yml
# Proxy and Wordpress services will not be restarted with this task.
# FTP transfers may be disrupted when this is run.
# No traffic can get through the DNS while this is run, 
#  so fallback DNS settings upstream (like your router) are a good idea.
# This cron task relieves any leaky memory or stuck traffic in the proxy and dns services.
##
rootdir=$PWD/../
cd $rootdir/dns/notracking 
./update
cd $rootdir
sudo docker-compose restart  >> ./ops/logs/cron.intranet.task.restart.log
