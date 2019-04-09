#! /bin/bash

##
# This generates an id_rsa key, injects the public key into the ssh server, copies the .pub file to the ftp/.ssh/public directory and then copies the private key to the ftp/.ssh/private directory.
# You will have to register the private SSH key with the ssh agent on your local machine after copying to your ~/.ssh or %USERPROFILE%/.ssh directory from the ./ftp/.ssh/private space.
##  Samba access can be found via \\[hostname]\ftp\.ssh
##  TO REGISTER THE PRIVATE KEY FROM YOUR OWN .SSH DIRECTORY
##  1. eval `ssh-agent`
##  2. ssh-add id_rsa
ssh-keygen -t rsa -N "" -f id_rsa
mv id_rsa.pub /ftp/.ssh/public/id_rsa_$HOSTNAME.pub
mv id_rsa /ftp/.ssh/private/id_rsa_$HOSTNAME
cp /ftp/.ssh/private/id_rsa /ftp/.ssh/id_rsa_$HOSTNAME
chmod 700 /ftp/.ssh/private
chmod 755 /ftp/.ssh/public
chmod 644 /ftp/.ssh/private/id_rsa
cp /ftp/.ssh/public/id_rsa_$HOSTNAME.pub /ftp/.ssh/authorized_keys/id_rsa_$HOSTNAME.pub
