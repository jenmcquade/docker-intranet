#! /bin/bash
projecturl=$(cat ./docker-wordpress/project.url)
giturl=$(cat ./docker-wordpress/wp-project.url)
rootdir=$PWD
randomWpPort=`shuf -i 8000-8999 -n 1`
randomMySqlPort=`shuf -i 3000-3999 -n 1`


echo "What would you like your site project name to be?"

read foldername

echo "Give your database a friendly name"

read dbname

echo "Is this a public or private site? Type 'p' for public or 'r' for restricted and press enter."

read pub

echo "What would you like your MySQL username to be? It will default to dbuser1"

read mysqluser

echo "Enter a password for MySQL user $mysqluser"

read mysqlpass1

echo "Re-enter your password for MySQL user $mysqluser"

read mysqlpass2

if [ $mysqlpass1 = $mysqlpass2 ]; then
  echo "Great! Setting up your $foldername project now..."
else 
  echo "Passwords didn't match!"
  ./bootstrap.sh
  exit 0
fi

echo "$foldername site code is being created..."

if [ ! -f "./dev/$foldername" ]; then
  mkdir ./dev/$foldername
else
  echo "This project name already exists!"
  exit 1
fi

mkdir ./.tmp

if [ ! -f "./dev/$foldername/wp-app" ]; then
  echo "Cloning $projecturl into $foldername..."

  devdir=$rootdir/dev/$foldername
  devappdir=$devdir/wp-app
  qadir=$rootdir/qa/$foldername
  qaappdir=$qadir/wp-app
  stgdir=$rootdir/stg/$foldername
  proddir=$rootdir/prod/$foldername
  prodappdir=$proddir/wp-app

  ## Clone the docker-wordpress project 
  git clone $projecturl.git $rootdir/.tmp/docker-wordpress

  ## Clone official WordPress git repo
  if [ ! -f "./dev/$foldername/wp-app/wp-content" ]; then
    git clone $giturl.git $rootdir/.tmp/wp-app
    if [ ! -f "$rootdir/.tmp/wp-app/wp-content" ]; then
      echo "WordPress couldn't be cloned from github!"
      exit 1
    fi
  fi

  ## Replace .env values for WP Ports to prepare docker
  cp $rootdir/docker-compose.tpl.yml $rootdir/.tmp/docker-compose.yml
  cp $rootdir/.env $rootdir/.tmp/.env
  sed -i 's/127.0.0.1/$IP/g' $rootdir/.tmp/.env
  sed -i 's/9999/$randomWpPort/g' $rootdir/.tmp/.env
  sed -i 's/3333/$randomMySqlPort/g' $roodir/.tmp/.env

  ## Move cloned github files to each environment directory
  cp -r $rootdir/.tmp/* $devdir/
  cp -r $rootdir/.tmp/* $qadir/
  cp -r $rootdir/.tmp/* $stgdir/
  cp -r $rootdir/.tmp/* $proddir/

  mkdir -p $devdir/config $qadir/config $stgdir/config $proddir/config

if [ -f "$rootdir/uploads" ]; then
  rm -rf $rootdir/dev/$foldername/wp-app/wp-content/uploads
  rm -rf $rootdir/qa/$foldername/wp-app/wp-content/uploads
  rm -rf $rootdir/stg/$fondername/wp-app/wp-content/uploads
  ln -s $rootdir/uploads $devappdir/wp-content/uploads
  ln -s $rootdir/uploads $qaappdir/wp-content/uploads
  ln -s $rootdir/uploads $stgappdir/wp-content/uploads
  ln -s $rootdir/uploads $prodappdir/wp-content/uploads
  echo "./uploads is now symlinked to your wp-content/uploads spaces"
fi

if [ -f "$rootdir/themes" ]; then
  rm -rf $rootdir/dev/$foldername/wp-app/wp-content/themes
  rm -rf $rootdir/qa/$foldername/wp-app/wp-content/themes
  rm -rf $rootdir/stg/$fondername/wp-app/wp-content/themes
  ln -s $rootdir/uploads $devappdir/wp-content/themes
  ln -s $rootdir/uploads $qaappdir/wp-content/themes
  ln -s $rootdir/uploads $stgappdir/wp-content/themes
  ln -s $rootdir/uploads $prodappdir/wp-content/themes
  echo "./themes is now symlinked to your wp-content/themes spaces"
fi

if [ -f "$rootdir/plugins" ]; then
  rm -rf $rootdir/dev/$foldername/wp-app/wp-content/plugins
  rm -rf $rootdir/qa/$foldername/wp-app/wp-content/plugins
  rm -rf $rootdir/stg/$fondername/wp-app/wp-content/plugins
  ln -s $rootdir/uploads $devappdir/wp-content/plugins
  ln -s $rootdir/uploads $qaappdir/wp-content/plugins
  ln -s $rootdir/uploads $stgappdir/wp-content/plugins
  ln -s $rootdir/uploads $prodappdir/wp-content/plugins
  echo "./plugins is now symlinked to your wp-content/plugins spaces"
fi

##########################################
## SSL AND NGINX STUFFS WILL GO HERE!!
##########################################

## !!import using a util.sh file after they're written!!

## Use AWS Route 53 to generate a new DNS A record

## Generate ./IP if it doesn't exist, so we can create public A records in AWS

## Point the new A record at the IP file value -- will need to symlink outside this directory

## Run certbot with aws plugin

## Generate a self-signed cert using openssl for the same domain

## Generate a self-signed cert for just the project as a domain

## Generate an nginx $foldername.conf to reverse proxy this project


#sudo docker run $DOCKER_ACCT/ssl-tools:v${IWC_INTRANET_VERSION} /scripts/run_certbot.sh


rm -rf $rootdir/.tmp/*

echo "Done!"
