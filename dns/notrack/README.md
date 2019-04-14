# Moving to GitLab
NoTrack blocklists have now been split from this project and moved to GitLab.  
New URLs are:  
NoTrack-Blocklist: https://gitlab.com/quidsup/notrack-blocklists/raw/master/notrack-blocklist.txt  
NoTrack-Malware: https://gitlab.com/quidsup/notrack-blocklists/raw/master/notrack-malware.txt  
   
   
# NoTrack
Tracking is absolutely rife on the Internet, on average 17 cookies are dropped by each website. Although you can block third party cookies, there are also more complex methods of tracking, such as:
 * Tracking Pixels 
 * HTML5 Canvas Fingerprinting 
 * AudioContext Fingerprinting 
 * WebRTC Local IP Discovery  
  
99 of the top 100 websites employ one or more of these forms of tracking.
  
NoTrack is a network-wide DNS server which blocks Tracking websites from creating cookies or sending tracking pixels. It works sinkholing known tracking and advertising sites to a web server running on the NoTrack device inside your network.
  
NoTrack currently works in Debian, Ubuntu, Redhat, and Fedora based Linux Distros.
You can use it on a Raspberry Pi with a fresh install of [Raspbian Lite](https://www.raspberrypi.org/downloads/raspbian/) to create a low power DNS server, which is more than capable of being used in a home or small office sized network.
  
# To Install:  
Tutorial Guide: https://youtu.be/MHsrdGT5DzE  
```bash
wget https://raw.githubusercontent.com/quidsup/notrack/master/install.sh  
bash install.sh
```

Point the DNS IP of all your systems to your NoTrack device.
Or setup DHCP on your NoTrack device using the instructional YouTube video provided.
  
Don't want to use the automated installer, no problem here is a manual [installer guide](https://github.com/quidsup/notrack/wiki/Custom-Install)
