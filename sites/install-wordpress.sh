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
  ./install-wordpress.sh
  exit 0
fi

echo "$foldername site code is being created..."

if [ ! -f "./$foldername" ]; then
  mkdir $foldername
  cd $rootdir/$foldername
else
  echo "This project name already exists!"
  exit 1
fi

if [ ! -f "./wp-app" ]; then
  echo "Cloning $projecturl into $foldername..."
  git clone $projecturl.git docker-wordpress-project
  cp -r ./docker-wordpress-project/* ./
  rm -rf ./docker-wordpress-project
  mkdir -p $rootdir/$foldername/config
  cp $rootdir/docker-wordpress/docker-compose.yml $rootdir/$foldername/docker-compose.yml
  cp $rootdir/docker-wordpress/config/php.conf.ini $rootdir/$foldername/config/php-conf.ini
  cp $rootdir/docker-wordpress/.env $rootdir/$foldername/.env
fi

## Replace .env values for WP Ports
sed -i 's/127.0.0.1/$IP/g' .env
sed -i 's/9999/$randomWpPort/g' .env
sed -i 's/3333/$randomMySqlPort/g' .env

if [ ! -f "./wp-app/wp-content" ]; then
  echo "Cloning $giturl into $foldername/wp-app .."
  git clone $giturl.git wp-app
  if [ ! -f "wp-app/wp-content" ]; then
    echo "WordPress couldn't be cloned from github!"
    exit 1
  fi
fi

if [ ! -f "./uploads" ]; then
  ln -s ./wp-app/wp-content/uploads files
  echo "$foldername/files is now symlinked to uploads directory"
fi

if [ ! -f "./themes" ]; then
  ln -s ./wp-app/wp-content/themes themes
  echo "$foldername/themes is now symlinked to themes directory"
fi

if [ ! -f "./plugins" ]; then
  ln -s ./wp-app/wp-content/plugins plugins
  echo "$foldername/plugins is now symlinked to plugins directory"
fi

echo "Done!"
