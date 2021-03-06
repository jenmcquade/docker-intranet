#!/bin/sh
#
# Description: Generates a Production Docker container,
#   then copies the o3dv container build files to ./public_build
#   then pushes newest instaproxy image to Heroku staging pipeline
#   then commits newest public_build folder to GitHub on staging branch
#     which then triggers a Heroku build of the staging branch
#   then returns to the original branch
#

cd $PWD

rm -rf ./public_build

docker-compose -f docker-compose.staging.yml down

date=`date +'%y.%m.%d %H:%M:%S'`

docker-compose -f docker-compose.staging.yml up --build -d

docker cp o3dv:/build/. ./public_build/

read -n1 -r -p "You can view the build at http://localhost:8080.  Or press space to continue the release..." key

if [ "$key" = '' ]; then

  docker-compose -f ./docker-compose.staging.yml down

  original_branch=`git branch | grep \*`

  git checkout staging

  git pull origin staging

  git rm -r ./public_build

  git add ./public_build 

  git commit -m "Staging Release at $date" ./.env ./public_build

  git push origin staging

  git checkout $original_branch

fi

exit 0


