#!/bin/bash

DOCKERFILE=docker/Dockerfile.dev
PROJECTNAME=json_api_symfony_dev
CONTAINERNAME=json_api_symfony
EXEC="docker exec -it $CONTAINERNAME /bin/bash"

build_base_docker_image () {
  docker build -f $DOCKERFILE -t $PROJECTNAME .
}

run_container () {
  docker run --name $CONTAINERNAME --mount type=bind,source="$(pwd)",target=/opt/app -d $PROJECTNAME
} 

stop_container () {
  docker stop $CONTAINERNAME > /dev/null 2>&1 
  docker container rm $CONTAINERNAME 
}

ssh_container(){
  $EXEC 
}

install_composer () {
  $EXEC -c "chmod u+x /var/www/html/install-composer.sh && /var/www/html/install-composer.sh" 
}

install_deps () {
  $EXEC -c "cd /opt/app && composer validate --strict && composer outdated --strict && composer install --no-dev --optimize-autoloader"
}

run_tests () {
  $EXEC -c "composer install --optimize-autoloader"
  $EXEC -c "composer lint"
  $EXEC -c "composer phpstan"
  $EXEC -c "phpdbg -qrr ./vendor/bin/phpunit"
}

case $1 in
      build) build_base_docker_image
             stop_container 
             run_container
             install_composer
             install_deps
             stop_container;;
      run) run_container ;;
      stop) stop_container ;;
      ssh) ssh_container ;;
      test) run_tests ;;
esac
