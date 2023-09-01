#!/bin/bash

DOCKERFILE=docker/php82.Dockerfile
PROJECTNAME=json_api_symfony_dev
CONTAINERNAME=json_api_symfony
EXEC="docker exec -it $CONTAINERNAME /bin/bash"
PHP_VERSIONS_TO_TEST=( "80" "81" "82" "83" )

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

install_deps () {
  $EXEC -c "cd /opt/app && composer validate --strict && composer outdated --strict && composer install --optimize-autoloader"
}

run_tests () {
#  $EXEC -c "phpdbg -qrr ./vendor/bin/phpunit"
  $EXEC -c "vendor/bin/phpunit tests"
}

run_qc () {
  $EXEC -c "composer lint"
  $EXEC -c "composer phpstan"
}

run_install () {
  $EXEC -c "composer install --optimize-autoloader"
}

run_test_php_versions () {
  for version in "${PHP_VERSIONS_TO_TEST[@]}"
  do
    docker stop docker/php$version.Dockerfile > /dev/null 2>&1
    docker container rm $CONTAINERNAME
    docker build -f docker/php$version.Dockerfile -t $PROJECTNAME .
    docker run --name $CONTAINERNAME --mount type=bind,source="$(pwd)",target=/opt/app -d $PROJECTNAME
    $EXEC -c "cd /opt/app && composer validate --strict && composer outdated --strict && composer install --optimize-autoloader"
    $EXEC -c "composer install --optimize-autoloader"
    docker stop docker/php$version.Dockerfile > /dev/null 2>&1
    docker container rm $CONTAINERNAME
  done
}

case $1 in
      build) build_base_docker_image
             stop_container
             run_container
             install_deps
             run_install
             stop_container;;
      run) run_container ;;
      stop) stop_container ;;
      ssh) ssh_container ;;
      test) run_tests ;;
      install) run_install ;;
      qc) run_qc ;;
      test_php_versions) run_test_php_versions ;;
esac
