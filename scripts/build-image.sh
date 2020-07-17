#!/bin/bash
set -e
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin

cd $1

docker build -t "radix-$1:$2" --build-arg SERVICE=$1 .
docker tag "radix-$1:$2" "endeavorb2b/radix-$1:$2"
docker push "endeavorb2b/radix-$1:$2"
docker image rm "radix-$1:$2"
