#!/usr/bin/bash
#Title : NoTrack Uninstaller
#Description : This script removes the files NoTrack created, and then return dnsmasq and lighttpd to their default configuration
#Author : QuidsUp
#Usage : sudo bash uninstall.sh

#User Configerable variables-----------------------------------------
readonly FOLDER_SBIN="/usr/local/sbin"
readonly FOLDER_ETC="/etc"

#Program Settings----------------------------------------------------
INSTALL_LOCATION="${HOME}/NoTrack"


#######################################
# Stop service
#    with either systemd or sysvinit or runit
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#######################################
service_stop() {
  if [[ -n $1 ]]; then
    echo "Stopping $1"
    if [ "$(command -v systemctl)" ]; then     #systemd
      sudo systemctl stop $1
    elif [ "$(command -v service)" ]; then     #sysvinit
      sudo service $1 stop
    elif [ "$(command -v sv)" ]; then          #runit
      sudo sv down $1
    else
      echo "Unable to stop services. Unknown service supervisor"
      exit 21
    fi
  fi
}


#Copy File-----------------------------------------------------------
CopyFile() {
  #$1 Source
  #$2 Target
  if [ -e "$1" ]; then
    echo "Copying $1 to $2"
    cp "$1" "$2"
  else
    echo "File $1 not found"
  fi
}
#Delete old file if it Exists----------------------------------------
DeleteFile() {
  if [ -e "$1" ]; then
    echo "Deleting file $1"
    rm "$1"    
  fi
}
#Delete old file if it Exists----------------------------------------
DeleteFolder() {
  if [ -d "$1" ]; then
    echo "Deleting folder $1"
    rm -rf "$1"    
  fi
}
#Find NoTrack--------------------------------------------------------
Find_NoTrack() {
  #This function finds where NoTrack is installed
  #1. Check current folder
  #2. Check users home folders
  #3. Check /opt/notrack
  #4. If not found then abort
  
  if [ -e "$(pwd)/notrack.sh" ]; then
    INSTALL_LOCATION="$(pwd)"  
    return 1
  fi
  
  for HomeDir in /home/*; do
    if [ -d "$HomeDir/NoTrack" ]; then 
      INSTALL_LOCATION="$HomeDir/NoTrack"
      break
    elif [ -d "$HomeDir/notrack" ]; then 
      INSTALL_LOCATION="$HomeDir/notrack"
      break
    fi
  done

  if [[ $INSTALL_LOCATION == "" ]]; then
    if [ -d "/opt/notrack" ]; then
      INSTALL_LOCATION="/opt/notrack"
    else
      echo "Error Unable to find NoTrack folder"
      echo "When NoTrack was installed in a custom location please specify it in uninstall.sh"
      echo "Aborting"
      exit 22
    fi
  fi
  
  return 1
}

#Main----------------------------------------------------------------

Find_NoTrack                                     #Where is NoTrack located?

if [[ "$(id -u)" != "0" ]]; then
  echo "Root access is required to carry out uninstall of NoTrack"
  echo "sudo bash uninstall.sh"
  exit 5
  #su -c "$0" "$@" - This could be an alternative for systems without sudo
fi

echo "This script will remove the files created by NoTrack, and then returns dnsmasq and lighttpd to their default configuration"
echo "NoTrack Installation Folder: $INSTALL_LOCATION"
echo
read -p "Continue (Y/n)? " -n1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
  echo "Aborting"
  exit 1
fi


service_stop dnsmasq
service_stop lighttpd
echo

echo "Deleting Symlinks for Web Folders"
echo "Deleting Sink symlink"
DeleteFile "/var/www/html/sink"
echo "Deleting Admin symlink"
DeleteFile "/var/www/html/admin"
echo

echo "Restoring Configuration files"
echo "Restoring Dnsmasq config"
CopyFile "/etc/dnsmasq.conf.old" "/etc/dnsmasq.conf"
echo "Restoring Lighttpd config"
CopyFile "/etc/lighttpd/lighttpd.conf.old" "/etc/lighttpd/lighttpd.conf"
echo "Removing Local Hosts file"
DeleteFile "/etc/localhosts.list"
echo

echo "Removing Log file rotator"
DeleteFile "/etc/logrotate.d/notrack"
echo

echo "Removing Cron job"
DeleteFile "/etc/cron.daily/notrack"             #Legacy
DeleteFile "/etc/cron.d/ntrk-parse"
echo

echo "Deleting NoTrack scripts"
echo "Deleting dns-log-archive"
DeleteFile "$FOLDER_SBIN/dns-log-archive"
echo "Deleting notrack"
DeleteFile "$FOLDER_SBIN/notrack"
echo "Deleting ntrk-exec"
DeleteFile "$FOLDER_SBIN/ntrk-exec"
echo "Deleting ntrk-pause"
DeleteFile "$FOLDER_SBIN/ntrk-pause"
echo "Deleting ntrk-parser"
DeleteFile "$FOLDER_SBIN/ntrk-parser"
echo

echo "Removing root permissions for www-data to launch ntrk-exec"
sed -i '/www-data/d' /etc/sudoers

echo "Deleting /etc/notrack Folder"
DeleteFolder "$FOLDER_ETC/notrack"
echo 

echo "Deleting Install Folder"
DeleteFolder "$INSTALL_LOCATION"
echo

echo "Finished deleting all files"
echo

echo "The following packages will also need removing:"
echo -e "\tdnsmasq"
echo -e "\tlighttpd"
echo -e "\tmariadb-server"
echo -e "\tphp"
echo -e "\tphp-cgi"
echo -e "\tphp-curl"
echo -e "\tphp-memcache"
echo -e "\tphp-mysql"
echo -e "\tmemcached"
echo
