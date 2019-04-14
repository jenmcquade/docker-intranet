#!/bin/bash
#Title : NoTrack Upgrader
#Description : 
#Author : QuidsUp
#Date : 2016-03-22
#Usage : ntrk-upgrade
#Last updated with NoTrack v0.8.10 - 02 July 2018

#Move file to /scripts at 0.8.5 TODO - happening at v1.0 moving to Gitlab

#######################################
# Constants
#######################################
readonly FILE_CONFIG="/etc/notrack/notrack.conf"

#######################################
# Global Variables
#######################################
INSTALL_LOCATION=""
USERNAME=""

#--------------------------------------------------------------------
# Copy File
#   Checks if Source file exists, then copies it to Destination
#
# Globals:
#   INSTALL_LOCATION
# Arguments:
#  $1 = Source
#  $2 = Destination
# Returns:
#   None
#--------------------------------------------------------------------
function copy_file() {  
  if [ -e "$INSTALL_LOCATION/$1" ]; then
    cp "$INSTALL_LOCATION/$1" "$2"
    echo "Copying $1 to $2"
  else
    echo "WARNING: Unable to find file $1"
  fi 
}


#--------------------------------------------------------------------
# Rename File
#   Renames Source file to Destination
#   Chmod 755 Destination file
#
# Globals:
#   None
# Arguments:
#  $1 = Error Message
#  $2 = Exit Code
# Returns:
#   None
#--------------------------------------------------------------------
function rename_file() {
  if [ -e "$1" ]; then
    mv "$1" "$2"
    chmod 755 "$2"    
  else
    echo "WARNING: Unable to rename file $1"
  fi
}


#--------------------------------------------------------------------
if [[ "$(id -u)" != "0" ]]; then
  echo "Root access is required to carry out upgrade of NoTrack"
  exit 21
fi

echo "Upgrading NoTrack"

for homefolder in /home/*; do
  if [ -d "$homefolder/NoTrack" ]; then 
    INSTALL_LOCATION="$homefolder/NoTrack"
    break
  elif [ -d "$homefolder/notrack" ]; then 
    INSTALL_LOCATION="$homefolder/notrack"
    break
  fi
done

if [[ $INSTALL_LOCATION == "" ]]; then
  if [ -d "/opt/notrack" ]; then
    INSTALL_LOCATION="/opt/notrack"
    USERNAME="root"
  elif [ -d "/root/notrack" ]; then
    INSTALL_LOCATION="/root/notrack"
    USERNAME="root"
  elif [ -d "/notrack" ]; then
    INSTALL_LOCATION="/notrack"
    USERNAME="root"
  else
    echo "Error Unable to find NoTrack folder"
    echo "Aborting"
    exit 22
  fi
else 
  USERNAME=$(grep "$homefolder" /etc/passwd | cut -d : -f1)
fi

echo "Install Location $INSTALL_LOCATION"
echo "Username: $USERNAME"
echo

#Alt command for sudoless systems
#su -c "cd /home/$USERNAME/$PROJECT ; svn update" -m "$USERNAME" 

sudo -u $USERNAME bash << ROOTLESS
if [ "$(command -v git)" ]; then                 #Utilise Git if its installed
  echo "Pulling latest updates of NoTrack using Git"
  cd "$INSTALL_LOCATION"
  git pull
  if [ $? != "0" ]; then                         #Git repository not found
    echo "Git repository not found"
    if [ -d "$INSTALL_LOCATION-old" ]; then      #Delete NoTrack-old folder if it exists
      echo "Removing old NoTrack folder"
      rm -rf "$INSTALL_LOCATION-old"
    fi
    echo "Moving $INSTALL_LOCATION folder to $INSTALL_LOCATION-old"
    mv "$INSTALL_LOCATION" "$INSTALL_LOCATION-old"
    echo "Cloning NoTrack to $INSTALL_LOCATION with Git"
    git clone --depth=1 https://github.com/quidsup/notrack.git "$INSTALL_LOCATION"
  fi
else                                             #Git not installed, fallback to wget
  echo "Downloading latest version of NoTrack from https://github.com/quidsup/notrack/archive/master.zip"
  wget -O /tmp/notrack-master.zip https://github.com/quidsup/notrack/archive/master.zip
  if [ ! -e /tmp/notrack-master.zip ]; then    #Check to see if download was successful
    #Abort we can't go any further without any code from git
    echo "Error Download from github has failed"
    exit 23
  fi
  
  if [ -d "$INSTALL_LOCATION" ]; then            #Check if NoTrack folder exists  
    if [ -d "$INSTALL_LOCATION-old" ]; then      #Delete NoTrack-old folder if it exists
      echo "Removing old NoTrack folder"
      rm -rf "$INSTALL_LOCATION-old"
    fi
    echo "Moving $INSTALL_LOCATION folder to $INSTALL_LOCATION-old"
    mv "$INSTALL_LOCATION" "$INSTALL_LOCATION-old"
  fi
 
  echo "Unzipping notrack-master.zip"
  unzip -oq /tmp/notrack-master.zip -d /tmp
  echo "Copying folder across to $INSTALL_LOCATION"
  mv /tmp/notrack-master "$INSTALL_LOCATION"
  echo "Removing temporary files"
  rm /tmp/notrack-master.zip                     #Cleanup
fi

ROOTLESS

if [ $? == 23 ]; then                            #Code hasn't downloaded
  exit 23
fi

echo
copy_file "notrack.sh" "/usr/local/sbin/"        #NoTrack.sh DEPRECATED at v1.0
copy_file "scripts/notrack.sh" "/usr/local/sbin/"          #NoTrack.sh
rename_file "/usr/local/sbin/notrack.sh" "/usr/local/sbin/notrack"

copy_file "ntrk-exec.sh" "/usr/local/sbin/"      #ntrk-exec.sh DEPRECATED at v1.0
copy_file "scripts/ntrk-exec.sh" "/usr/local/sbin/"        #ntrk-exec.sh
rename_file "/usr/local/sbin/ntrk-exec.sh" "/usr/local/sbin/ntrk-exec"

copy_file "ntrk-pause.sh" "/usr/local/sbin/"     #ntrk-pause.sh DEPRECATED at v1.0
copy_file "scripts/ntrk-pause.sh" "/usr/local/sbin/"       #ntrk-pause.sh
rename_file "/usr/local/sbin/ntrk-pause.sh" "/usr/local/sbin/ntrk-pause"

copy_file "ntrk-upgrade.sh" "/usr/local/sbin/"   #ntrk-upgrade.sh DEPRECATED at v1.0
copy_file "scripts/ntrk-upgrade.sh" "/usr/local/sbin/"     #ntrk-upgrade.sh
rename_file "/usr/local/sbin/ntrk-upgrade.sh" "/usr/local/sbin/ntrk-upgrade"

copy_file "scripts/ntrk-parse.sh" "/usr/local/sbin/"       #ntrk-parse.sh
rename_file "/usr/local/sbin/ntrk-parse.sh" "/usr/local/sbin/ntrk-parse"
echo "Finished copying scripts"
echo
  
sudocheck=$(grep www-data /etc/sudoers)                    #Check sudo permissions for lighty
if [[ $sudocheck == "" ]]; then
  echo "Adding NoPassword permissions for www-data to execute script /usr/local/sbin/ntrk-exec as root"
  echo -e "www-data\tALL=(ALL:ALL) NOPASSWD: /usr/local/sbin/ntrk-exec" | tee -a /etc/sudoers
fi

#v0.8.1 - Add user_agent table to sql db 
mysql --user=ntrk --password=ntrkpass -D ntrkdb -e "ALTER TABLE lightyaccess ADD COLUMN IF NOT EXISTS referrer TEXT AFTER uri_path;"
mysql --user=ntrk --password=ntrkpass -D ntrkdb -e "ALTER TABLE lightyaccess ADD COLUMN IF NOT EXISTS user_agent TEXT AFTER referrer;"
mysql --user=ntrk --password=ntrkpass -D ntrkdb -e "ALTER TABLE lightyaccess ADD COLUMN IF NOT EXISTS remote_host TEXT AFTER user_agent;"

#v0.8.1 - Add user_agent collection to lighttpd.conf
if [[ $(grep '"%{%s}t|%V|%r|%s|%b"' /etc/lighttpd/lighttpd.conf) != "" ]]; then
  sed -i 's/"%{%s}t|%V|%r|%s|%b"/"%{%s}t|%V|%r|%s|%b|%{Referer}i|%{User-Agent}i|%h"/' /etc/lighttpd/lighttpd.conf
  echo "lighttpd needs restarting: please restart lighttpd service"
fi

if [[ $(grep '"%{%s}t|%V|%r|%s|%b|%{Referer}i|%{User-Agent}i"' /etc/lighttpd/lighttpd.conf) != "" ]]; then
  sed -i 's/"%{%s}t|%V|%r|%s|%b|%{Referer}i|%{User-Agent}i"/"%{%s}t|%V|%r|%s|%b|%{Referer}i|%{User-Agent}i|%h"/' /etc/lighttpd/lighttpd.conf
  echo "lighttpd needs restarting: please restart lighttpd service"
fi


if [ -e "$FILE_CONFIG" ]; then                             #Remove Latestversion number from Config file
  echo "Removing version number from Config file"
  grep -v "LatestVersion" "$FILE_CONFIG" > /tmp/notrack.conf
  mv /tmp/notrack.conf "$FILE_CONFIG"
  echo
fi
  
echo "NoTrack update complete"
