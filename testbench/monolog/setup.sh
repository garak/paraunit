#!/bin/bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

docker build -t paraunit_image_tb_monolog $DIR

docker rm -f paraunit_container_tb_monolog
docker run -d -v $DIR/../../:/tmp/monolog/vendor/facile-it/paraunit/ --name paraunit_container_tb_monolog -ti paraunit_image_tb_monolog bash

sleep 1

docker exec -ti -u paraunit paraunit_container_tb_monolog zsh
