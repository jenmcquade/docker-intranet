#!/bin/bash 
#Title : NoTrack Live DNS Log Archiver
#Description : Loads contents of /var/log/notrack.log into "live" SQL DB
#Author : QuidsUp
#Date Created : 03 October 2016
#Usage : sudo ntrk-parse

#process_todaylog can take a long time to run. In order to prevent loss of DNS queries
#the log file is loaded into an array, and then immediately zeroed out.
#Processing is done on the array from memory
#Between 04:00 to 04:20 Live table is copied to Historic table
#For systems not running 24/7 Live table is copied after data is over 1 day old
#In event of STATUS_INCOGNITO being set, blank out log files and don't store anything

#######################################
# Constants
#######################################
readonly FILE_LIGHTYLOG="/var/log/lighttpd/access.log"
readonly FILE_DNSLOG="/var/log/notrack.log"
readonly FILE_CONFIG="/etc/notrack/notrack.conf"
readonly MAXAGE=88000                                      #Just over 1 day in seconds
readonly MINLINES=50
readonly VERSION="0.8.9"

readonly STATUS_INCOGNITO=8                                #STATUS_INCOGNITO is the only status relevent to ntrk-parse

readonly USER="ntrk"
readonly PASSWORD="ntrkpass"
readonly DBNAME="ntrkdb"

CURRENT_DAY="$(date +"%d")"
CURRENT_DATE="$(date +"%Y-%m-%d")"
YESTERDAY_DATE="$(date -d "1 day ago" "+%Y-%m-%d")"

#######################################
# Global Variables
#######################################
simpleurl=""
status=1

declare -a logarray

declare -A commonsites
commonsites["cloudfront.net"]=true
commonsites["googleusercontent.com"]=true
commonsites["googlevideo.com"]=true
commonsites["cedexis-radar.net"]=true
commonsites["gvt1.com"]=true
commonsites["deviantart.net"]=true
commonsites["deviantart.com"]=true
commonsites["ampproject.net"]=true
commonsites["steamcontent.com"]=true
commonsites["tumblr.com"]=true
commonsites["userstorage.mega.co.nz"]=true

#--------------------------------------------------------------------
# Check If Running as Root and if Script is already running
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function check_root() {
  local pid=""
  pid=$(pgrep ntrk-parse | head -n 1)            #Get PID of first notrack process

  if [[ "$(id -u)" != "0" ]]; then
    echo "This script must be run as root"
    exit 5
  fi
  
  #Check if another copy of notrack is running
  if [[ $pid != "$$" ]] && [[ -n $pid ]] ; then  #$$ = This PID    
    echo "ntrk-parse already running under Pid $pid"
    exit 111
  fi
}

#--------------------------------------------------------------------
# Copy Live Table to Historic
#
# Globals:
#   USER, PASSWORD, DBNAME
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function copy_table() {
  mysql -sN --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "INSERT INTO historic SELECT NULL,log_time,sys,dns_request,dns_result FROM live ORDER BY log_time;"
  
  if [ $? == 0 ]; then
    echo "Successfully copied Live table to Historic"
  else
    echo "Error $? failed to copy Live table"
  fi
}

#--------------------------------------------------------------------
# Check Log Age
#   Query timestamp of first value in Live table
#
# Globals:
#   USER, PASSWORD, DBNAME
# Arguments:
#   None
# Returns:
#   0 - In date
#   >0 - Number of days old
#--------------------------------------------------------------------
function check_logage() {
  local log_time=""
  local log_epoch=0
  local unixtime=0
  
  log_time=$(mysql -sN --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "SELECT log_time FROM live ORDER BY log_time LIMIT 1;")
  echo "Log Time:$log_time"
  
  if [[ $log_time == "" ]]; then                 #Anything returned? CHECK THIS VALUE
    echo "No log time found"
    return 0                                     #Error, but treat as 0 - ok
  fi
  
  log_epoch=$(date +"%s" -d "$log_time")         #Convert YYYY-MM-DD hh:mm:ss to epoch
  unixtime=$(date +"%s")                         #Get current epoch time
  
  if [ $((unixtime-log_epoch)) -gt $MAXAGE ]; then         #Check age
    if [ "$(((unixtime-log_epoch)/86400))" -gt 254 ]; then #Avoid error values > 254
      return 254
    fi
    return "$(((unixtime-log_epoch)/86400))"     #Return value is days
  fi
  
  return 0                                       #Otherwise return 0 - ok
}

#--------------------------------------------------------------------
# Delete Lighty Access Table
#   1. Delete all rows in the lightyaccess Table
#   2. Reset Counter
#
# Globals:
#   USER, PASSWORD, DBNAME
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function delete_lighty() {
  mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "DELETE LOW_PRIORITY FROM lightyaccess;"
  mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "ALTER TABLE lightyaccess AUTO_INCREMENT = 1;"
}


#--------------------------------------------------------------------
# Delete Live Table
#   1. Delete all rows in the Live Table
#   2. Reset Counter
#
# Globals:
#   USER, PASSWORD, DBNAME
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function delete_live() {
  mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "DELETE LOW_PRIORITY FROM live;"
  mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "ALTER TABLE live AUTO_INCREMENT = 1;"
}

#--------------------------------------------------------------------
# Check If mysql or MariaDB is installed
#   exits if not installed
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function is_sql_installed() {
  if [ -z "$(command -v mysql)" ]; then
    echo "NoTrack requires MySql or MariaDB to be installed"
    echo "Run install.sh -sql"
    exit 60
  fi  
}

#--------------------------------------------------------------------
# Load Config File
#   1. Read SQL password (future) and Suppress from Config
#   2. Explode values of Suppress into a Temp array, then add to commonsites
#
# Globals:
#   commonsites
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function load_config() {
  local suppress_str=""
  local url=""
  local -a temp

  if [ ! -e "$FILE_CONFIG" ]; then
    echo "Config $FILE_CONFIG missing"
    return
  fi
  
  echo "Reading Config File"
  while IFS='= ' read -r key value               #Seperator '= '
  do
    if [[ ! $key =~ ^\ *# && -n $key ]]; then
      value="${value%%\#*}"    # Del in line right comments
      value="${value%%*( )}"   # Del trailing spaces
      value="${value%\"*}"     # Del opening string quotes 
      value="${value#\"*}"     # Del closing string quotes 
        
      case "$key" in
        Suppress) suppress_str="$value";;
        status) status="$value";;
      esac            
    fi
  done < $FILE_CONFIG  
  
  unset IFS
    
  IFS=',' read -ra temp <<< "${suppress_str}"    #Explode string into temp array
  unset IFS
  for url in "${temp[@]}"; do                    #Read each item of temp array
    commonsites[$url]=true                       #Add users Config[Suppress] to commonsites
  done  
}


#--------------------------------------------------------------------
# Load Lighty Log file into array
#   This function is used to ensure that losses are minimised while we process notrack.log
#   1. Read Lighty and add values into logarray
#   2. Empty log file

# Globals:
#   logarray
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function load_lightylog() {
  echo "Loading lighty access log file into array"
  while IFS=$'\n' read -r line
  do
    logarray+=("$line")
  done < "$FILE_LIGHTYLOG"
    
  cat /dev/null > "$FILE_LIGHTYLOG"              #Empty log file
}

#--------------------------------------------------------------------
# Load DNS Log file into array
#   This function is used to ensure that losses are minimised while we process notrack.log
#   1. Read notrack.log and add values into logarray
#   2. Empty log file

# Globals:
#   logarray
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function load_todaylog() {
  echo "Loading notrack log file into array"
  while IFS=$'\n' read -r line
  do
    logarray+=("$line")
  done < "$FILE_DNSLOG"
    
  cat /dev/null > "$FILE_DNSLOG"                 #Empty log file
}


#--------------------------------------------------------------------
# Process Lighty Log
#   1. Read each line of logarray and pattern match with regex 
#   2. Negate /admin and /favicon.ico
#   3. Add queries to querylist and systemlist arrays
#   4. Find what happened to each query
#   5. Build string for SQL entry
#   6. Echo result into SQL
# Globals:
#   logarray, USER, PASSWORD, DBNAME
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------

  
#Lighty log line consists of:
#1: \d{1,23} - 64bit Time value
#2: [NOT |] One or more times Left-hand side of URL (before /)
#3: (GET|POST) GET or POST
#4: [NOT space] Any number of times Right-hand side of URL (after /)
#HTTP 1.1 or 2.0
#200 - HTTP Ok (Not interested in 304,404)
#5: URI Path [NOT |]
#6: Referrer [NOT |]
#7: User Agent [NOT |]
#8: Remote Host [NOT |]
  
function process_lightylog() {
  local line=""
  local log_time=0
  local site=""
  local http_method=""
  local uri_path=""
  local referrer=""
  local user_agent=""
  local remote_host=""
  
  echo "Processing lighty log file"
    
  for line in "${logarray[@]}"; do               #Read whole logarray
    #echo "$line"                                #Uncomment for debugging
    if [[ $line =~ ^([0-9]{1,23})\|([^\|]+)\|(GET|POST)[[:space:]]([^[:space:]]+)[[:space:]]HTTP\/[0-9]\.[0-9]\|200\|[0-9]+\|([^\|]+)\|([^\|]+)\|(.+) ]]; then    
      log_time="${BASH_REMATCH[1]}"              #Allocate variables from BASH_REMATCH
      site="${BASH_REMATCH[2]}"
      http_method="${BASH_REMATCH[3]}"
      uri_path="${BASH_REMATCH[4]}"
      referrer="${BASH_REMATCH[5]}"
      user_agent="${BASH_REMATCH[6]}"
      remote_host="${BASH_REMATCH[7]}"
      if [[ ! $uri_path =~ ^(\/admin|\/favicon\.ico) ]]; then  #Negate admin access
        mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "INSERT INTO lightyaccess (id,log_time,site,http_method,uri_path,referrer,user_agent,remote_host) VALUES ('NULL',FROM_UNIXTIME('$log_time'), '$site', '$http_method', '$uri_path', '$referrer', '$user_agent', '$remote_host')"
      fi    
    fi
  done
  
  unset IFS  
}


#--------------------------------------------------------------------
# Process Today Log
#   1. Read each line of logarray and pattern match with regex 
#   2. Add queries to querylist and systemlist arrays
#   3. Find what happened to each query
#   4. Build string for SQL entry
#   5. Echo result into SQL
# Globals:
#   logarray, simpleurl, USER, PASSWORD, DBNAME
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------

#Dnsmasq log line consists of:
#0 - Month (3 characters)
#1 - Day (d or dd) - Group 1
#2 - Time (dd:dd:dd) - Group 2
#3 - dnsmasq[d{1-6}]
#4 - Function (query, forwarded, reply, cached, config) - Group 3
#5 - Query Type [A/AAA] - Group 4
#5 - Website Requested - Group 5
#6 - Action (is|to|from) - Group 6
#7 - Return value - Group 7

function process_todaylog() {
  local dedup_answer=""
  local dns_result=""
  local line=""
  local url=""
  local -A querylist
  local -A systemlist  

  echo "Processing log file"
    
  for line in "${logarray[@]}"; do
    if [[ $line =~ ^[A-Z][a-z][a-z][[:space:]][[:space:]]?([0-9]{1,2})[[:space:]]([0-9]{2}\:[0-9]{2}\:[0-9]{2})[[:space:]]dnsmasq\[[0-9]{1,6}\]\:[[:space:]](query|reply|config|\/etc\/localhosts\.list)(\[[A]{1,4}\])?[[:space:]]([A-Za-z0-9\.\-]+)[[:space:]](is|to|from)[[:space:]](.*)$ ]]; then
      url="${BASH_REMATCH[5]}"
      
      if [[ ${BASH_REMATCH[3]} == "query" ]]; then
        if [[ ${BASH_REMATCH[4]} == "[A]" ]]; then              #Only IPv4 to prevent double query entries
          if [[ ${BASH_REMATCH[1]} == "$CURRENT_DAY" ]]; then   #Fix for dealing with date transition 23:50 - 00:10            
            querylist[$url]="$CURRENT_DATE ${BASH_REMATCH[2]}"  #Add current date and time to query array
          else
            querylist[$url]="$YESTERDAY_DATE ${BASH_REMATCH[2]}" #Add yesterday date and time to query array
          fi
          systemlist[$url]="${BASH_REMATCH[7]}"             #Add IP to system array
        fi      
      elif [[ $url != "$dedup_answer" ]]; then   #Simplify processing of multiple IP addresses returned
        dedup_answer="$url"                      #Deduplicate answer
        if [ "${querylist[$url]}" ]; then        #Does answer match a query?
          if [[ ${BASH_REMATCH[3]} == "reply" ]]; then dns_result="A"    #Allowed
          elif [[ ${BASH_REMATCH[3]} == "config" ]]; then dns_result="B" #Blocked
          elif [[ ${BASH_REMATCH[3]} == "/etc/localhosts.list" ]]; then dns_result="L"
          fi
          
          simplify_url "$url"                    #Simplify with commonsites
          
          if [[ $simpleurl != "" ]]; then        #Add row into SQL Table
            echo "INSERT INTO live (id,log_time,sys,dns_request,dns_result) VALUES ('null','${querylist[$url]}', '${systemlist[$url]}', '$simpleurl', '$dns_result')" | mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME"
          fi
                    
          unset querylist[$url]                  #Delete value from querylist
          unset systemlist[$url]                 #Delete value from system list
        fi      
      fi
    fi
  done
  unset IFS  
}
#--------------------------------------------------------------------
# Show Log Age
#   Echos result of check_logage
#
# Globals:
#   None
# Arguments:
#   $1 - Age of earliest entry in Live
# Returns:
#   None
#--------------------------------------------------------------------
function show_logage() {
  if [ "$1" == 0 ]; then echo "In date"
  else
    echo "Out of date: $1 Days old"
  fi
}

#--------------------------------------------------------------------
#Show Help
function show_help() {
  echo "NoTrack DNS Log Parser"
  echo "Usage: sudo ntrk-parse"
  echo
  echo "The following options can be specified:"
  echo -e "  -a, --age\tCheck Age of Live table"
  echo -e "  -c, --copy\tCopy Live table to Historic table"
  echo -e "  -h, --help\tThis Help"
  echo -e "  -v, --version\tDisplay version number"
  echo -e "  --delete-lighty\tDelete contents of Lighty table"
  echo -e "  --delete-live\tDelete contents of Live table"
}
#--------------------------------------------------------------------
#Show Version
function show_version() {
  echo "NoTrack live DNS Archiver v$VERSION"
  echo
}

#--------------------------------------------------------------------
# Simplify URL
#   1: Drop www (its unnecessary and not all websites use it now)
#   2. Extract domain.tld, including double-barrelled domains
#   3. Check if site is to be suppressed (present in commonsites)
# Globals:
#   simpleurl
#   commonsites
# Arguments:
#   $1 - URL To Simplify
# Returns:
#   via simpleurl global variable
#-------------------------------------------------------------------- 
function simplify_url() {
  local baseurl=""
  simpleurl=""
  
  baseurl="$1"
    
  if [[ ${baseurl:0:4} == "www." ]]; then
    baseurl="${baseurl:4}"
  fi
  
  if [[ $baseurl =~ [A-Za-z0-9\-]{2,63}\.(gov\.|org\.|co\.|com\.)?[A-Za-z0-9\-]{2,63}$ ]]; then
    if [ ${commonsites[${BASH_REMATCH[0]}]} ]; then
      simpleurl="*.${BASH_REMATCH[0]}"
    else
      simpleurl="$baseurl"
    fi
  fi 
}

#--------------------------------------------------------------------
# Trim Lighty Access Table
#   1. Delete all rows in the lightyaccess Table older than 31 Days
#
# Globals:
#   USER, PASSWORD, DBNAME
# Arguments:
#   None
# Returns:
#   None
#--------------------------------------------------------------------
function trim_lighty() {
  mysql --user="$USER" --password="$PASSWORD" -D "$DBNAME" -e "DELETE FROM lightyaccess WHERE log_time < NOW() - INTERVAL 60 DAY;"  
}


#--------------------------------------------------------------------
#Main
if [ "$1" ]; then                                #Have any arguments been given
  if ! options="$(getopt -o achv -l age,copy,delete-access,delete-live,help,version -- "$@")"; then
    # something went wrong, getopt will put out an error message for us
    exit 6
  fi

  set -- $options

  while [ $# -gt 0 ]
  do
    case $1 in
      -a|--age)
        check_logage
        show_logage $?
        exit 0
      ;;
      -c|--copy)
        copy_table
        exit 0
      ;;
      --delete-lighty)
        delete_lighty
        exit 0
      ;;
      --delete-live)
        delete_live
        exit 0
      ;;
      -h|--help)
        show_help
        exit 0
      ;;
      -v|--version) 
        show_version
        exit 0
      ;;
      (--) 
        shift
        break
      ;;
      (-*)         
        echo "$0: error - unrecognized option $1"
        exit 6
      ;;
      (*) 
        break
      ;;
    esac
    shift
  done
fi

if [[ $CURRENT_DAY =~ ^0([0-9])$ ]]; then         #Trim leading zero from single digit date
  CURRENT_DAY="${BASH_REMATCH[1]}"  
fi

#Between 04:00 - 04:20 Its time to copy Live to Historic
if [[ "$(date +'%H')" == "04" ]]; then    
  if [ "$(date +'%M')" -lt 20 ]; then
    copy_table                                   #Copy Live to Historic
    delete_live
    trim_lighty                                  #Trim lighty access table
    exit 112                                     #No rush to parse log right now
  fi
fi

#Alternate option to anyone not running their system 24/7
check_logage                                     #Is Live older than MAXAGE?
if [ $? -gt 0 ]; then                            #More than 0 is age in days
  copy_table                                     #Copy Live to Historic
  delete_live
  #trim_lighty                                    #Optional to add
  sleep 2s
fi

if [ "$(wc -l "$FILE_DNSLOG" | cut -d " " -f 1)" -lt $MINLINES ]; then
  echo "Not much in $FILE_DNSLOG, exiting"
  exit 110
fi

check_root                                       #Are we running as root?
is_sql_installed
load_config                                      #Load users config

if (( ($status & $STATUS_INCOGNITO ) >0 )); then           #Bitwise checks if incongnito enabled
  #echo "Incognito mode set"                               #Debugging
  cat /dev/null > "$FILE_LIGHTYLOG"                        #Empty lighty log file
  cat /dev/null > "$FILE_DNSLOG"                           #Empty DNS log file
  exit 0                                                   #Exit safely to make sure no data is recorded in Incognito mode
fi

#Make sure there is something in lighttpd access log 
if [ "$(wc -l "$FILE_LIGHTYLOG" | cut -d " " -f 1)" -gt 2 ]; then
  load_lightylog                                 #Load lighttpd log file into array
  process_lightylog                              #Process and add log to SQL table  
fi

logarray=()                                      #Empty logarray for reuse

load_todaylog                                    #Load log file into array
process_todaylog                                 #Process and add log to SQL table

