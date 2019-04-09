Vagrant.configure("2") do |config|

  config.vm.provision :docker

  ###
  #  BOX CONFIGURATIONS
  ###

  config.vm.box = "ubuntu/bionic64"
  # config.vm.network "private_network", type: "dhcp"
  config.vm.box_check_update
  config.vm.hostname = "intranet"

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
wget \
xfce4 \
xfce4-goodies \
librsvg2-common \
gvfs \
pinentry-doc \
tightvncserver
myuser="vagrant"
mypasswd="*ntr@NET$"
mkdir -p /home/$myuser/.vnc
echo $mypasswd | vncpasswd -f > /home/$myuser/.vnc/passwd
chmod 0600 /home/$myuser/.vnc/passwd
echo -e "#!/bin/bash\n xrdb $HOME/.Xresources\n startxfce4 &" | tee /home/$myuser/.vnc/xstartup
sudo chmod +x /home/$myuser/.vnc/xstartup
vncserver &
EOC

# Pull latest Intranet files from github and start up the main Docker containers
config.vm.provision "shell", inline: <<-EOC
sh /home/vagrant/intranet/generate-vagrant-env.sh
if [ -f ./environment.sh ]; then
  sh ./environment.sh
fi
cd /home/vagrant/intranet
git pull origin master
sudo docker-compose pull 
cd ops
sudo docker-compose pull
EOC

end
