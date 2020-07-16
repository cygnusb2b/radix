#!/bin/bash
set -e
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin

mv services ../
mkdir services
mv ../services/$1 services/

mv "services/$1/Dockerfile" Dockerfile
docker build -t "radix-$1:$2" --build-arg SERVICE=$1 .

mv ../services/* services/
rm -rf ../services

docker tag "radix-$1:$2" "endeavorb2b/radix-$1:$2"
docker push "endeavorb2b/radix-$1:$2"
docker image rm "radix-$1:$2"
