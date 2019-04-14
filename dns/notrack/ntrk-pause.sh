#!/bin/bash
#Title : NoTrack Pause
#Description : NoTrack Pause can pause/stop/start blocking in NoTrack by moving blocklists away from /etc/dnsmasq.d
#Author : QuidsUp
#Date : 2016-02-23
#Last updated with notrack 0.8.9
#Usage : ntrk-pause [--pause | --stop | --start | --status]

#Move file to /scripts at 0.8.5 TODO

#######################################
# Constants
#######################################
readonly FILE_CONFIG="/etc/notrack/notrack.conf"
readonly NOTRACK_LIST="/etc/dnsmasq.d/notrack.list"
readonly NOTRACK_TEMP="/tmp/ntrkpause/notrack.list"

readonly STATUS_ENABLED=1
readonly STATUS_DISABLED=2
readonly STATUS_PAUSED=4
readonly STATUS_INCOGNITO=8
readonly STATUS_NOTRACKRUNNING=64
readonly STATUS_ERROR=128

#######################################
# Global Variables
#######################################
pause_time=0
incognito=0


#--------------------------------------------------------------------
# Backup Lists
#   Backup all NoTrack blocklists from /etc/dnsmasq.d to /tmp/ntrkpause
#
# Globals:
#   NOTRACK_LIST, NOTRACK_TEMP
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function backup_lists() {
  create_folder "/tmp/ntrkpause"
  move_file "$NOTRACK_LIST" "$NOTRACK_TEMP"
}


#--------------------------------------------------------------------
# Change Status
#   1: Checks if status exists in config
#   2: Modify status if exists
#   3: Add status
#
# Globals:
#   incognoito   
# Arguments:
#   $1 - Status
#   $2 - Unpause time (optional)
# Returns:
#   None
#--------------------------------------------------------------------
function change_status() {
  local newstatus=$1
  let "newstatus += $incognito"
  
  if [ ! -e "$FILE_CONFIG" ]; then                         #Does config exist?
    touch "$FILE_CONFIG"
  fi
  
  if [[ $(grep "status" "$FILE_CONFIG") != "" ]]; then
    sed -i "s/^\(status *= *\).*/\1$newstatus/" $FILE_CONFIG
  else
    echo "status = $newstatus" >> $FILE_CONFIG
  fi
  
  if [ "$2" ]; then
    if [[ $(grep "unpausetime" "$FILE_CONFIG") != "" ]]; then
      sed -i "s/^\(unpausetime *= *\).*/\1$2/" $FILE_CONFIG
    else
      echo "unpausetime = $2" >> $FILE_CONFIG
    fi
  fi
    
}

#--------------------------------------------------------------------
# Check Root
#   1: Checks if running as root
#   2: Checks if script is already running, and then closes older running script
#
# Globals:
#   None
# Arguments:
#   $1 - Folder to create
# Returns:
#   None
#--------------------------------------------------------------------
function check_root() {
  local pid=""
  pid=$(pgrep ntrk-pause | head -n 1)            #Get PID of first ntrk-pause process

  if [[ "$(id -u)" != "0" ]]; then
    echo "Error this script must be run as root"
    exit 5
  fi

  #Check if another copy of ntrk-pause is running, and terminate it
  if [[ $pid != "$$" ]] && [[ -n $pid ]] ; then  #$$ = This PID
    echo "Ending ntrk-pause process $pid"
    kill -9 "$pid"
  fi
}


#--------------------------------------------------------------------
# Create Folder
#   Creates a folder if it doesn't exist
#
# Globals:
#   None
# Arguments:
#   $1 - Folder to create
# Returns:
#   None
#--------------------------------------------------------------------
function create_folder {
  if [ ! -d "$1" ]; then                         #Does folder exist?
    echo "Creating folder: $1"                   #Tell user folder being created
    mkdir "$1"                                   #Create folder
  fi
}


#--------------------------------------------------------------------
# Delete Folder
#   Deletes a folder if it exists
#
# Globals:
#   None
# Arguments:
#   $1 - Folder to delete
# Returns:
#   None
#--------------------------------------------------------------------
function delete_folder {
  if [ -d "$1" ]; then                           #Does folder exist?
    echo "Deleting folder: $1"                   #Tell user folder being deleted
    rm -r "$1"                                   #Delete folder
  fi
}


#--------------------------------------------------------------------
# Get Status
#   Checks status of config and if blocklists exist
#
# Globals:
#   FILE_CONFIG, NOTRACK_LIST, NOTRACK_TEMP
# Arguments:
#   None
# Returns:
#   Status of blocking
#   See STATUS consts at top
#--------------------------------------------------------------------
function get_status() {
  local status=0

  if [[ $(pgrep notrack) != "" ]]; then          #Is NoTrack running?
    return $STATUS_NOTRACKRUNNING
  fi

  if [ -e "$FILE_CONFIG" ]; then                 #Does config exist?
    if [[ $(grep "status" "$FILE_CONFIG") != "" ]]; then
      status=$(grep -i status /etc/notrack/notrack.conf | cut -f2 -d= | cut -f2 -d\ )
      
      if (( ($status & $STATUS_INCOGNITO ) >0 )); then     #Bitwise checks if incongnito enabled
        incognito=$STATUS_INCOGNITO                        #Store incongnito result        
      fi
      if (( ($status & $STATUS_PAUSED ) >0 )); then        #Bitwise checks
        return $STATUS_PAUSED
      fi
      if (( ($status & $STATUS_DISABLED ) >0 )); then
        return $STATUS_DISABLED
      fi
      if (( ($status & $STATUS_ENABLED ) >0 )); then
        return $STATUS_ENABLED
      fi
    fi
  fi
    
  #No config file or unknown status, check blocklist exists
  if [ -e "$NOTRACK_LIST" ]; then
    return $STATUS_ENABLED
  elif [ -e "$NOTRACK_TEMP" ]; then
    return $STATUS_DISABLED
  else                                                     #No idea - no blocking set, no config
    return $STATUS_ERROR
  fi

  return $STATUS_ERROR                                     #Shouldn't get to this point
}


#--------------------------------------------------------------------
# Move File
#   Checks if Source file exists, then copies it to Destination
#
# Globals:
#   None
# Arguments:
#   $1 = Source
#   $2 = Destination
# Returns:
#   0 on success, 1 when file not found
#--------------------------------------------------------------------
function move_file() {
  if [ -e "$1" ]; then
    mv "$1" "$2"
    echo "Moving $1 to $2"
    return 0
  else
    echo "WARNING: Unable to find file $1"
    return 1
  fi
}


#######################################
# Restart service
#    with either systemd or sysvinit or runit
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#######################################
service_restart() {
  if [[ -n $1 ]]; then
    echo "Restarting $1"
    if [ "$(command -v systemctl)" ]; then                 #systemd
      sudo systemctl restart "$1"      
    elif [ "$(command -v service)" ]; then                 #sysvinit
      sudo service "$1" restart
    elif [ "$(command -v sv)" ]; then                      #runit
      sudo sv restart "$1"
    else
      echo "Unable to restart services. Unknown service supervisor"
      exit 21
    fi
  fi
}


#--------------------------------------------------------------------
# Restore Lists
#   1: Restore NoTrack blocklist from /tmp/ntrkpause to /etc/dnsmasq.d
#   2: If list doesn't exist, then run NoTrack
#   3: Remove status from config
#
# Globals:
#   NOTRACK_LIST, NOTRACK_TEMP, FILE_CONFIG
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function restore_lists() {
  if [ -e "$NOTRACK_TEMP" ]; then
    move_file "$NOTRACK_TEMP" "$NOTRACK_LIST"
    delete_folder "/tmp/ntrkpause"
  else
    echo "Unable to find old blocklists, running NoTrack"
    /usr/local/sbin/notrack -f
  fi  
}


#--------------------------------------------------------------------
# Show Help
#   Display help, then exit
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
show_help() {
  echo "Usage: ntrk-pause [Option]"
  echo "ntrk-pause Starts and Stops blocking with NoTrack"
  echo
  echo "The following options can be specified:"
  echo -e "  -d, --stop\t\tStop NoTrack"
  echo -e "  -h, --help\t\tDisplay this help and exit"
  echo -e "  -p, --pause [Number]\tPause NoTrack for [Number] of Minutes"
  echo -e "  -s, --start\t\tStart NoTrack from Either Paused of Stopped state"
  echo -e "  --status\t\tDisplay current status of ntrk-pause"
  echo
  exit 0
}


#--------------------------------------------------------------------
# Show Status
#   Displays the result of get_status, then exit
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
show_status() {
  get_status

  case $? in
    $STATUS_ENABLED)
      echo "Status $STATUS_ENABLED: Blocking Enabled"
      ;;
    $STATUS_PAUSED)
      echo "Status $STATUS_PAUSED: Blocking Paused"
      ;;
    $STATUS_DISABLED)
      echo "Status $STATUS_DISABLED: Blocking Disabled"
      ;;
    $STATUS_ERROR)
      echo "Status $STATUS_ERROR: Old config exists, but status unknown"
      ;;
    $STATUS_NOTRACKRUNNING)
      echo "Status $STATUS_NOTRACKRUNNING: NoTrack already running"
      ;;
  esac
  exit 0
}


#--------------------------------------------------------------------
# Disable Blocking - Stop
#   1. Check if running as Root user
#   2. Get Status of ntrk-pause
#   3. Following action depends on the result of get_status
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
disable_blocking() {
  check_root

  get_status
  case "$?" in
    $STATUS_ENABLED)                                       #Enable > Disabled
      echo "Disabling blocking"
      backup_lists
      change_status $STATUS_DISABLED
      service_restart dnsmasq
      ;;
    $STATUS_PAUSED)                                        #Paused > Disabled
      echo "Switching from Paused to Disabled"
      sed -i "s/^\(Status *= *\).*/\1Stop/" $FILE_CONFIG
      ;;
    $STATUS_DISABLED)
      echo "NoTrack blocking already Disabled"
      return $STATUS_DISABLED
      ;;
    $STATUS_ERROR)
      echo "Unknown Status"
      exit $STATUS_ERROR
      ;;
    $STATUS_NOTRACKRUNNING)
      echo "NoTrack already running"
      exit $STATUS_NOTRACKRUNNING
      ;;
  esac
}


#--------------------------------------------------------------------
# Enable Blocking - Start
#   1. Check if running as Root user
#   2. Get Status of ntrk-pause
#   3. Following action depends on the result of get_status
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
enable_blocking() {
  check_root

  get_status
  case "$?" in
    $STATUS_ENABLED)                                       #Enable > Enabled
      echo "NoTrack blocking already enabled"
      ;;
    $STATUS_PAUSED | $STATUS_DISABLED)                     #Paused | Stop > Enable
      echo "Enabling NoTrack"
      change_status $STATUS_ENABLED 0
      restore_lists
      service_restart dnsmasq
      ;;
    $STATUS_ERROR)
      echo "Unknown Status, running NoTrack to enable blocking"
      /usr/local/sbin/notrack
      ;;
    $STATUS_NOTRACKRUNNING)
      echo "NoTrack already running"
      exit $STATUS_NOTRACKRUNNING
      ;;
  esac
}


#--------------------------------------------------------------------
# Pause Blocking
#   1. Check if running as Root user
#   2. Get Status of ntrk-pause
#   3. Following action depends on the result of get_status
#   4. Sleep for ntrk-pause $2 minutes
#   5. Restore blocklists
# Globals:
#   pause_time
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function pause_blocking() {
  local unpause_time=0

  #Calculate unpause time, based on (Current Epoch time + (ntrk-pause $2 * 60))
  unpause_time=$(date +%s)                                 #Epoch time now
  let unpause_time+="($pause_time * 60)"

  check_root

  get_status
  case $? in
    $STATUS_ENABLED)                                       #Enabled > Paused
      backup_lists
      service_restart dnsmasq
      change_status $STATUS_PAUSED $unpause_time      
      ;;
    $STATUS_PAUSED)                                        #Paused > Different Pause Time
      echo "Changing Pause time"
      change_status $STATUS_PAUSED $unpause_time      
      ;;
    $STATUS_DISABLED)                                      #Disabled > Paused
      echo "Switching from Disabled to Paused"
      change_status $STATUS_PAUSED $unpause_time      
      ;;
    $STATUS_ERROR)                                         #Unknown
      echo "Old config exists, but status unknown"
      exit $STATUS_ERROR
      ;;
    $STATUS_NOTRACKRUNNING)
      echo "NoTrack already running"
      exit $STATUS_NOTRACKRUNNING
      ;;
  esac

  echo
  echo "Sleeping for $pause_time minutes"
  sleep "${pause_time}m"

  restore_lists
  change_status $STATUS_ENABLED 0
  service_restart dnsmasq
}



#Main----------------------------------------------------------------

if [ "$1" ]; then                         #Have any arguments been given
  if ! options=$(getopt -o hdsp: -l help,stop,start,status,pause: -- "$@"); then
    # something went wrong, getopt will put out an error message for us
    exit 1
  fi

  set -- $options

  while [ $# -gt 0 ]
  do
    case $1 in
      -h|--help)
        show_help
        ;;
      -d|--stop)
        disable_blocking
        ;;
      -s|--start)
        enable_blocking
        ;;
      -p|--pause)
        pause_time=$(sed "s/'//g" <<< "$2")      #Remove single quotes from $2
        pause_blocking
        shift
        ;;
      --status)
        show_status
        ;;
      (--) shift; break;;
      (-*) echo "$0: error - unrecognized option $1" 1>&2; exit 6;;
      (*) break;;
    esac
    shift
  done
else                                             #No commands passed
  echo "Checking status of NoTrack"
  #No instructions given by user, the following will happen based on the result of get_status
  #a. Status Nothing - Pause for 15 Minutes
  #b. Status Unknown - Run NoTrack
  #c. Status Paused - Unpause
  #d. Status Stopped - Start

  get_status
  case $? in
    $STATUS_ENABLED)
      echo "Pausing NoTrack for 15 minutes"
      pause_time=15
      pause_blocking
      ;;
    $STATUS_PAUSED)
      echo "Unpausing NoTrack"
      enable_blocking
      ;;
    $STATUS_DISABLED)
      echo "Enabling NoTrack"
      enable_blocking
      ;;
    $STATUS_NOTRACKRUNNING)
      echo "Wait. NoTrack processing blocklists"      
      ;;
    $STATUS_ERROR)
      echo "Pause status unknown. Running NoTrack"
      /usr/local/sbin/notrack -f
      ;;
  esac
fi

