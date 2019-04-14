#!/bin/bash
#Title : NoTrack Exec
#Description : NoTrack Exec takes jobs that have been written to from
# A low privilege user, e.g. www-data, and then carries out the job
# at root level.
#Author : QuidsUp
#Date : 2015-02-02
#Usage : Write jobs to /tmp/ntrk-exec.txt, then launch ntrk-exec


#######################################
# Global Variables
#######################################


#######################################
# Constants
#######################################
readonly ACCESSLOG="/var/log/ntrk-admin.log"
readonly FILE_CONFIG="/etc/notrack/notrack.conf"
readonly FILE_EXEC="/tmp/ntrk-exec.txt"
readonly TEMP_CONFIG="/tmp/notrack.conf"
readonly DNSMASQ_CONF="/etc/dnsmasq.conf"

readonly USER="ntrk"
readonly PASSWORD="ntrkpass"
readonly DBNAME="ntrkdb"

#--------------------------------------------------------------------
# Block Message
#   Sets Block message for sink page
#
# Globals:
#   None
# Arguments:
#   $1 Message
# Returns:
#   None
#--------------------------------------------------------------------
function block_message() {
  if [[ $1 == "message" ]]; then
    echo 'Setting Block message Blocked by NoTrack'
    echo '<p>Blocked by NoTrack</p>' | tee /var/www/html/sink/index.html &> /dev/null
  elif [[ $1 == "pixel" ]]; then
    echo 'Setting Block message to pixel'
    echo '<img src="data:image/gif;base64,R0lGODlhAQABAAAAACwAAAAAAQABAAA=" alt="" />' | tee /var/www/html/sink/index.html &> /dev/null
  fi
  
  if getent passwd www-data > /dev/null 2>&1; then  #default group is www-data
    sudo chown -hR www-data:www-data /var/www/html/sink    
  elif getent passwd http > /dev/null 2>&1; then    #Arch uses group http
    sudo chown -hR http:http /var/www/html/sink    
  fi
  
  sudo chmod -R 775 /var/www/html/sink
}


#--------------------------------------------------------------------
# Copy Black list
#   Copies temp blacklist to /etc/notrack
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function copy_blacklist() {
  if [ -e "/tmp/blacklist.txt" ]; then
    chown root:root /tmp/blacklist.txt
    chmod 644 /tmp/blacklist.txt
    echo "Copying /tmp/blacklist.txt to /etc/notrack/blacklist.txt"
    mv /tmp/blacklist.txt /etc/notrack/blacklist.txt
    echo  
  else
    echo "/tmp/blacklist.txt missing"
  fi
}


#--------------------------------------------------------------------
# Copy White list
#   Copies temp whitelist to /etc/notrack
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function copy_whitelist() {
  if [ -e "/tmp/whitelist.txt" ]; then
    chown root:root /tmp/whitelist.txt
    chmod 644 /tmp/whitelist.txt
    echo "Copying /tmp/whitelist.txt to /etc/notrack/whitelist.txt"
    mv /tmp/whitelist.txt /etc/notrack/whitelist.txt    
  else
    echo "/tmp/whitelist.txt missing"
  fi
}


#--------------------------------------------------------------------
# Copy TLD Lists
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function copy_tldlists() {
  if [ -e "/tmp/domain-blacklist.txt" ]; then
    chown root:root /tmp/domain.txt
    chmod 644 /tmp/domain-blacklist.txt
    echo "Copying /tmp/domain-blacklist.txt to /etc/notrack/domain-blacklist.txt"
    mv /tmp/domain-blacklist.txt /etc/notrack/domain-blacklist.txt
    echo
  fi

  if [ -e "/tmp/domain-whitelist.txt" ]; then
    chown root:root /tmp/domain-whitelist.txt
    chmod 644 /tmp/domain-whitelist.txt
    echo "Copying /tmp/domain-whitelist.txt to /etc/notrack/domain-whitelist.txt"
    mv /tmp/domain-whitelist.txt /etc/notrack/domain-whitelist.txt    
  fi
}


#--------------------------------------------------------------------
# Create Access Log
#
# Globals:
#   ACCESSLOG
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function create_accesslog() {
  if [ ! -e "$ACCESSLOG" ]; then
    echo "Creating $ACCESSLOG"
    touch "$ACCESSLOG"
    chmod 666 "$ACCESSLOG"
  fi
}


#--------------------------------------------------------------------
# Delete History
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
delete_history() {
  echo "Deleting contents of Historic table"
  echo "DELETE LOW_PRIORITY FROM historic;" | mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME"
  echo "ALTER TABLE historic AUTO_INCREMENT = 1;" | mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME"
  
  echo "Deleting Log Files in /var/log/lighttpd"
  rm /var/log/lighttpd/*                         #Delete all files in lighttpd log folder
  touch /var/log/lighttpd/access.log             #Create new access log and set privileges
  chown www-data:root /var/log/lighttpd/access.log
  chmod 644 /var/log/lighttpd/access.log
  touch /var/log/lighttpd/error.log              #Create new error log and set privileges
  chown www-data:root /var/log/lighttpd/error.log
  chmod 644 /var/log/lighttpd/error.log
}


#--------------------------------------------------------------------
# Add Value
#   Add Value to SQL Config Table
# Globals:
#   None
# Arguments:
#   1: type
#   2: option_name
#   3: option_value
#   4: option_enabled
# Returns:
#   None
#--------------------------------------------------------------------
function addvalue() {
  local enabled=1
    
  if [[ $4 == "#" ]]; then
    enabled=0  
  fi
  
  #echo "'$1','$2','$3','$enabled'"
  mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "INSERT INTO config (config_id, config_type, option_name, option_value, option_enabled) VALUES ('NULL', '$1', '$2', '$3', '$enabled');"  
}


#--------------------------------------------------------------------
# Read Dnsmasq Config for DHCP values
#   1: Create config table (if necessary)
#   2: Delete any old values in config table
#   3: Read through dnsmasq.conf
#
# Globals:
#   USER, PASSWORD, DBNAME
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function read_dhcp() {
  local dhcp_enabled=false
  local line=""
  local previous_line=""
  
  mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "CREATE TABLE IF NOT EXISTS config (config_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, config_type TINYTEXT, option_name TINYTEXT, option_value TEXT, option_enabled BOOLEAN);"  
  
  mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "DELETE FROM config WHERE config_type = 'dhcp';"
  
  while IFS=$'\n' read -r line
  do
    if [[ $line =~ ^dhcp-range\=([^,]+),([^,]+),(.+)$ ]]; then
      dhcp_enabled=true
      addvalue "dhcp" "dhcp_enabled" "" ""
      addvalue "dhcp" "start_ip" "${BASH_REMATCH[1]}" ""
      addvalue "dhcp" "end_ip" "${BASH_REMATCH[2]}" ""
      addvalue "dhcp" "lease_time" "${BASH_REMATCH[3]}" ""
    elif [[ $line =~ ^dhcp-option\=3,(.+)$ ]]; then
      addvalue "dhcp" "router_ip" "${BASH_REMATCH[1]}" ""
    elif [[ $line =~ ^(#?)([^\=]+)\=?(.*)$ ]]; then
      case "${BASH_REMATCH[2]}" in
        dhcp-authoritative|log-dhcp)
          addvalue "dhcp" "${BASH_REMATCH[2]}" "" "${BASH_REMATCH[1]}"
          ;;
        dhcp-host)           #Option + Value
          if [[ ${BASH_REMATCH[1]} == "" ]]; then
            if [[ ${previous_line:0:1} == "#" ]]; then
              addvalue "dhcp" "${BASH_REMATCH[2]}" "${previous_line:1},${BASH_REMATCH[3]}" "${BASH_REMATCH[1]}"
            else
              addvalue "dhcp" "${BASH_REMATCH[2]}" "${BASH_REMATCH[3]}" "${BASH_REMATCH[1]}"
            fi
          fi
          ;;
      esac
      previous_line="$line"
    fi
  done < "$DNSMASQ_CONF" 
  
  if [[ $dhcp_enabled == false ]]; then          #Add default values if DHCP is disabled
    addvalue "dhcp" "dhcp_enabled" "" "#"
    addvalue "dhcp" "start_ip" "192.168.0.50" ""
    addvalue "dhcp" "end_ip" "192.168.0.150" ""
    addvalue "dhcp" "lease_time" "12h" ""
    addvalue "dhcp" "router_ip" "192.168.0.254" ""
  fi
  unset IFS
}


#--------------------------------------------------------------------
# Read Dnsmasq Config
#   1: Create config table (if necessary)
#   2: Delete any old values in config table
#   3: Read through dnsmasq.conf
#
# Globals:
#   USER, PASSWORD, DBNAME
# Arguments:
#   None
# Returns:
#   None
# Regex:
#   1: Exclude Comment line
#   2: Group 1 - Disabled option, Group 2 - option_name, Group 3 - option_value
#--------------------------------------------------------------------
function read_dnsmasq() {
  local line=""
  mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "CREATE TABLE IF NOT EXISTS config (config_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, config_type TINYTEXT, option_name TINYTEXT, option_value TEXT, option_enabled BOOLEAN);"  
  
  mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "DELETE FROM config WHERE config_type = 'dnsmasq';"
  
  while IFS=$'\n' read -r line
  do
    if [[ ! $line =~ ^#[[:space:]] ]]; then
      if [[ $line =~ ^(#?)([^\=]+)\=?(.*)$ ]]; then
        #echo "${BASH_REMATCH[1]} - ${BASH_REMATCH[2]} - ${BASH_REMATCH[3]}"
        case "${BASH_REMATCH[2]}" in
          bogus-priv|bind-interfaces|dnssec|dnssec-check-unsigned|filterwin2k) #Option
            addvalue "dnsmasq" "${BASH_REMATCH[2]}" "" "${BASH_REMATCH[1]}"
            ;;
          enable-ra|log-queries|log-async|no-resolv)                     #Option
            addvalue "dnsmasq" "${BASH_REMATCH[2]}" "" "${BASH_REMATCH[1]}"
            ;;
          resolv-file|addn-hosts|cache-size|conf-file|ipset|interface)   #Option +  Value
            addvalue "dnsmasq" "${BASH_REMATCH[2]}" "${BASH_REMATCH[3]}" "${BASH_REMATCH[1]}"
            ;;
          listen-address|local-ttl|log-facility)           #Option + Value
            addvalue "dnsmasq" "${BASH_REMATCH[2]}" "${BASH_REMATCH[3]}" "${BASH_REMATCH[1]}"
            ;;
          server)                                          #Enabled values only
            if [[ ${BASH_REMATCH[1]} == "" ]]; then
              addvalue "dnsmasq" "server" "${BASH_REMATCH[3]}" ""
            fi
            ;;
        esac
      fi
    fi
  done < "$DNSMASQ_CONF"
  
  unset IFS
}


#--------------------------------------------------------------------
# Parsing Time
#   Update CRON Job parsing interval for ntrk-parse
#   
# Globals:
#   FILE_CONFIG
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function parsing_time() {
  interval=""
  interval=$(grep "ParsingTime" "$FILE_CONFIG")  #Load single value from Config
  if [[ -n $interval ]]; then                    #Anything found?
    interval=$(cut -d "=" -f 2 <<< $interval | cut -d " " -f 2) #Remove 'Parsing = '
    if [[ $interval =~ ^[1-9][0-9]?$ ]]; then    #Valid one or two digit number?
      echo "Setting Cron job interval of $interval minutes for ntrk-parse"
      echo -e "*/$interval * * * *\troot\t/usr/local/sbin/ntrk-parse" | sudo tee /etc/cron.d/ntrk-parse &> /dev/null
    else
      echo "Invalid value for ParsingTime in $FILE_CONFIG"
    fi
  else
    echo "Error: Value for ParsingTime not found in $FILE_CONFIG"
  fi  
}

#--------------------------------------------------------------------
# Write Dnsmasq Config
#
# Globals:
#   USER, PASSWORD, DBNAME
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function write_dnsmasq() {
  echo "here"
}


#--------------------------------------------------------------------
# Update Config
#
# Globals:
#   FILE_CONFIG, TEMP_CONFIG
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function update_config() {
  if [ -e "/tmp/notrack.conf" ]; then
    chown root:root "$TEMP_CONFIG"
    chmod 644 /tmp/notrack.conf      
    echo "Copying $TEMP_CONFIG to $FILE_CONFIG"
    mv "$TEMP_CONFIG" "$FILE_CONFIG"
    echo
  fi
}


#--------------------------------------------------------------------
# Upgrade NoTrack
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function upgrade_notrack() {
  if [ -e /usr/local/sbin/ntrk-upgrade ]; then
    echo "Running NoTrack Upgrade"
    sudo /usr/local/sbin/ntrk-upgrade #2>&1
  else
    echo "NoTrack Upgrade is missing, using fallback notrack.sh"
    sudo /usr/local/sbin/notrack -u
  fi
}


#Main----------------------------------------------------------------

if [[ $(whoami) == "www-data" ]]; then           #Check if launced from web server without root user   DEPRECATED
  echo "Error. ntrk-exec needs to be run as root"
  echo "run 'notrack --upgrade' to set permissions in sudoers file"
  exit 2
fi

if [[ "$(id -u)" != "0" ]]; then                 #Check if running as root
  echo "Error this script must be run as root"
  exit 2
fi



if [ "$1" ]; then                         #Have any arguments been given
  if ! Options=$(getopt -o hps -l accesslog,bm-msg,bm-pxl,delete-history,force,parsing,run-notrack,restart,save-conf,shutdown,upgrade,read:,write:,pause:,copy: -- "$@"); then
    # something went wrong, getopt will put out an error message for us
    exit 1
  fi

  set -- $Options

  while [ $# -gt 0 ]
  do
    case $1 in
      -h)
        echo "Help"
      ;;
      --accesslog)
        create_accesslog
      ;;      
      --bm-msg)
        block_message "message"
      ;;
      --bm-pxl)
        block_message "pixel"
      ;;
      --copy)
        if [[ $2 == "'black'" ]]; then
          copy_blacklist
        elif [[ $2 == "'white'" ]]; then
          copy_whitelist
        elif [[ $2 == "'tld'" ]]; then
          copy_tldlists
        else
          echo "Invalid file"
        fi      
      ;;
      --delete-history)
        delete_history
      ;;
      --force)
        /usr/local/sbin/notrack --force > /dev/null &
      ;;
      -p)                                        #Play
        /usr/local/sbin/ntrk-pause --start  > /dev/null &
      ;;
      --parsing)
        parsing_time;
      ;;
      --pause)
        pausetime=$(sed "s/'//g" <<< "$2")       #Remove single quotes
        echo "$pausetime"        
        /usr/local/sbin/ntrk-pause --pause "$pausetime"  > /dev/null &
      ;;
      --read)
        if [[ $2 == "'dnsmasq'" ]]; then
          read_dnsmasq
        elif [[ $2 == "'dhcp'" ]]; then
          read_dhcp
        fi
      ;;
      --restart)
        reboot > /dev/null &
      ;;
      -s)                                        #Stop
        /usr/local/sbin/ntrk-pause --stop  > /dev/null &
      ;;
      --shutdown)
        shutdown now  > /dev/null &
      ;;      
      --run-notrack)        
        /usr/local/sbin/notrack > /dev/null &
      ;;
      --save-conf)
        update_config
      ;;
      --upgrade)
        upgrade_notrack
      ;;
      --write)
        if [[ $2 == "'dnsmasq'" ]]; then
          write_dnsmasq 
        fi         
      ;;
      (--) shift; break;;
      (-*) echo "$0: error - unrecognized option $1" 1>&2; exit 6;;
      (*) break;;
    esac
    shift
  done
else 
  echo "No arguments given"
fi
