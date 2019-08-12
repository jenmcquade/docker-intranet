Vagrant.configure("2") do |config|

  ###
  # INDIE WEB CONSULTING DOCKER INTRANET
  ###

  #@ Virtual Agnostic Development Environment and Registry (VADER) @#
  
  # "We use what we build, and we trust what we use." #

  #
  # DESCRIPTION
  #
  #  -- "Virtual Agnostic" means IWC is supporting two (count 'em, 2!) CPU architectures in this repo, both running the same Linux kernel across all Linux OS software releases. So, v1.0.0 (Jonah) corresponds to Linux kernel 4.4, which is supported by Android 9 Pie, Debian, Ubuntu, Chromium OS and Armbian
  #  -- This Vagrant VBox builder is for x64 machines, and is intended as a substitute for clients who wish to run IWC software without IWC hardware
  #  -- The original inspiration for this builder was to run the IWC Intranet on a Windows Home 64-bit OS, with no need for Hyper-V or Windows container management to run its server software.
  #  -- This Ubuntu Bionic Beaver environment won't allow you to build arm64/aar64 code in some cases (mostly Docker) without modification
  #  -- In a lot of cases, prerequisite libraries for builders are synonymous across architectures of a Linux distro. So, you can try searching Debian and Alpine libraries online if you need to adapt an arm64 builder for x64
  #  -- This environment will allow you to run and build x64 versions of the Docker Intranet for IWC products that run in a hybrid Cloud
  #  -- x64 code builds can run in most Cloud providers with minimal supervision, using Docker Deploy (No Kubernetes experience required)
  #  -- arm64 code builds run natively on IWC embedded products (No Vagrant/Vbox). Some embedded software libraries may not run on other hardware, and IWC only validates its software on its own hardware
  #  -- You can change config.vm.box base box and the code repo should still pull down into the box during provisioning from this file
  #  -- As with most things FOSS with a GNU slant: You break it, you buy IT. IWC has not patents, but its warantees and guarantees are attached to its service level agreements
  # -- Please use community forums for free support, because that's how IWC also learns cool stuff, responds to feature requests, and contributes back to the FOSS community

  ###

  ###
  # PROVISIONING CONFIGURATIONS
  ###

  ###
  # OS use cases
  # "One Kernel to Pop Them All"
  # IWC Docker Intranet v1.0.0 Jonah
  # Targeting Linux Kernel 4.4 variations
  # Stable branches across libraries are implemented for production containers
  # On occassion, we use edge branches when appropriate and testable.
  # We clone and fork public repos. When we're sharing, we provide project.url file to the original implementation of a project or library.
  # We do not use community forks or universes
  ###

  #  -- Ubuntu Xenial (16.04 LTS) is Linux Kernel 4.4, which is also supported by Android Pie. However, other build tools and libraries are better suited to run on Bionic. So, Vagrant runs on Bionic, but we only build Ubuntu Docker images as high as Xenial.
  #  -- Debian builds on top of kernel 4.14 and has limited arm64 library support. 
  #  -- Some GC++ libraries that Docker arm64 builders rely on require Debian arm64 libraries to build (Ubuntu/Alpine might not have corresponding build libraries), so Debian Stretch is implemented in some Docker build stages.
  #  -- Linux Alpine is hopefully always the container OS for Docker in this project, unless Dockerfile stipulates otherwise.
  #  -- Linux Alpine 3.8.2 is the last version to include Linux Kernel 4.4 (4.14.89). So, that's what we target in our Docker Alpine containers.
  
  ###
  # FIRMWARE
  ###
  
  #  -- Burning tools in the ./util folder work when the host machine is either x64 or arm64. The tools are Linux-only, and will run under Vagrant to burn multi-boot OS images to the SD card location specified in ./.env. 
  #  -- Windows image burning tools are scattered across the Web, but they do not burn the appropriate boot partition files that Amlogic uses in its firmware. We don't develop or test on Apple Mac OS.
  # -- Use the 'df' command line tool on the host Linux machine to find the /dev/ location of the SD card, which is usually /dev/sdd.
  # You can NOT (!) pass the burning tools a partition directory; it has to be the device source, which will be mounted and unmounted during the burning process to create partitions. 
  # -- Data is sacred, and server logs are written to the SD card when not in a hybrid cloud state. It's not written to the eMMC to conserve OS storage. Please backup any personal data before running burn tools. 
  # -- Your data will become unrecoverable during the SD card burning process, and fails a lot of the time. This is true even with Class 1 cards, if they've lost their conductivity from wear and tear.
  ##
  ###

  ###
  ## VAGRANT ENVIRONMENT COMPOSITION BELOW
  ###
  config.vm.box = "ubuntu/bionic64"
  config.vm.network "private_network", type: "dhcp"
  config.vm.box_check_update
  config.vm.hostname = "iwc.intranet"
  config.vm.provision :docker
  config.vm.boot_timeout = 30000

  ## You can force a publicly-accessible IP for your virtual machine instead of restricing to a private network. You would then forward ports in your router to the virtual machine.
  # config.vm.network "public_network", ip: "192.168.0.17"

  # default router
  # config.vm.provision "shell",
    # run: "always",
    # inline: "route add default gw 192.168.0.1"

  # default router ipv6
  # config.vm.provision "shell",
    # run: "always",
    # inline: "route -A inet6 add default gw fc00::1 eth1"

  # delete default gw on eth0
  # config.vm.provision "shell",
    # run: "always",
    # inline: "eval `route -n | awk '{ if ($8 ==\"eth0\" && $2 != \"0.0.0.0\") print \"route del default gw \" $2; }'`"


  ###
  # PORT FORWARDING CONFIGURATIONS
  ###

  config.vm.network "forwarded_port", guest: 80, host: 8080 # Primary Nginx resolvers
  for i in 8081...8100
    config.vm.network "forwarded_port", guest: i, host: i # Anything custom
  end
  config.vm.network "forwarded_port", guest: 3306, host: 3366 # MySQL
  config.vm.network "forwarded_port", guest: 21, host: 2121 # FTP
  # config.vm.network "forwarded_port", guest: 22, host: 2222 # SSH, SCP, RDP over SCP 
  config.vm.network "forwarded_port", guest: 25, host: 2525 # SMTP 
  config.vm.network "forwarded_port", guest: 53, host: 5353 # DNS
  config.vm.network "forwarded_port", guest: 5000, host: 5000 # Docker registry
  config.vm.network "forwarded_port", guest: 9000, host: 9999 # PHP-FPM
  config.vm.network "forwarded_port", guest: 443, host: 4433 # SSL
  config.vm.network "forwarded_port", guest: 139, host: 1339 # SMB TCP1
  config.vm.network "forwarded_port", guest: 445, host: 4444 # SMB TCP2
  config.vm.network "forwarded_port", guest: 1935, host: 1999 # RTMP streams
  config.vm.network "forwarded_port", guest: 3389, host: 3333 # Windows Remote Desktop
  config.vm.network "forwarded_port", guest: 5901, host: 5999 # RDP
  config.vm.network "forwarded_port", guest: 8000, host: 8888 #DynamoDb
  config.vm.network "forwarded_port", guest: 137, host: 1337, protocol: "udp" # SMB UDP1
  config.vm.network "forwarded_port", guest: 873, host: 8873, protocol: "udp" # SMB UDP2


  ###
  # SHARED FOLDER CONFIGURATION
  ###

  ## MORE INFO ABOUT SHARED FOLDERS IS HERE: 
  ##   https://www.vagrantup.com/docs/synced-folders/nfs.html
  ## Synced folders -- Samba requires your host auth credentials when creating the box
  
  ## IF YOU USE WINDOWS, UNCOMMENT THIS LINE
  # config.vm.synced_folder "./", "/home/vagrant/intranet", type: "smb"
  
  ## IF YOU USE LINUX, USE THIS LINE
  config.vm.synced_folder ".", "/home/vagrant/intranet", type: "nfs"

  ## AS A FALLBACK, YOU CAN USE RSYNC BY UNCOMMENTING THIS LINE
  # config.vm.synced_folder ".", "/home/vagrant/intranet", type: "rsync", rsync__exclude: ".git/"

  ###
  # VIRTUALBOX PROVIDER CONFIGURATIONS
  ### 

  config.vm.provider "virtualbox" do |v|
    v.name = "intranet"
    v.memory = 2048
    v.cpus = 1
    v.linked_clone = true if Gem::Version.new(Vagrant::VERSION) >= Gem::Version.new('1.8.0')
    v.customize ["modifyvm", :id, "--cpuexecutioncap", "50"]
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
  end

  ###
  # GUEST INITIALIZATION CONFIGURATIONS
  ###

# Docker installation scripts

config.vm.provision "shell", inline: <<-EOC
  sudo curl -L "https://github.com/docker/compose/releases/download/1.24.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
  sudo chmod +x /usr/local/bin/docker-compose
  sudo ln -s /usr/local/bin/docker-compose /usr/bin/docker-compose
  sudo docker --version
  sudo docker-compose version 
EOC

# Update Ubuntu and grab some handy tools
config.vm.provision "shell", inline: <<-EOC
  sudo apt-get update 
  sudo apt-get upgrade
  sudo apt-get install -y \
  dictionaries-common \
  aspell \
  aspell-en \
  miscfiles \
  git \
  vim \
  nano \
  wget
EOC

  # xfce4 \
  # xfce4-goodies \
  # librsvg2-common \
  # gvfs \
  # pinentry-doc \
  # tightvncserver
  # myuser="vagrant"
  # mypasswd="*ntr@NET$"
  # mkdir -p /home/$myuser/.vnc
  # echo $mypasswd | vncpasswd -f > /home/$myuser/.vnc/passwd
  # chmod 0600 /home/$myuser/.vnc/passwd
  # echo -e "#!/bin/bash\n xrdb $HOME/.Xresources\n startxfce4 &" | tee /home/$myuser/.vnc/xstartup
  # sudo chmod +x /home/$myuser/.vnc/xstartup
  # vncserver &

# Pull latest Intranet files from github and start up the main Docker containers
config.vm.provision "shell", inline: <<-EOC
  sh /home/vagrant/intranet/generate-vagrant-env.sh
  if [ -f /home/vagrant/intranet/environment.sh ]; then
    sh /home/vagrant/intranet/environment.sh
  fi
  cd /home/vagrant/intranet
  git pull origin master
EOC

##
# DEPLOY SCRIPT INITIATION
##
# ! This is commented until arm64 Docker builders are all pushed to Dockerhub #
# sudo docker-compose pull 
# sudo docker-compose up -d
# cd ops
# sudo docker-compose pull
# sudo docker-compose up -d
#EOC

end
