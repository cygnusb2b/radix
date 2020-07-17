#!/bin/bash
set -e
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin

DOCKER_ORG="${DOCKER_ORG-endeavorb2b}"

cd $1

docker build -t "radix-$1:$2" --build-arg SERVICE=$1 .
docker tag "radix-$1:$2" "$DOCKER_ORG/radix-$1:$2"
docker push "$DOCKER_ORG/radix-$1:$2"
docker image rm "radix-$1:$2"
