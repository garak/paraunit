#!/bin/bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

docker build -t paraunit_image_tb_symfony $DIR

docker rm -f paraunit_container_tb_symfony
docker run -d -v $DIR/../../:/tmp/symfony/vendor/facile-it/paraunit/ --name paraunit_container_tb_symfony -ti paraunit_image_tb_symfony bash

sleep 1

docker exec -ti -u paraunit paraunit_container_tb_symfony zsh
