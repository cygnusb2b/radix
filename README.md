radix
=====

[![CircleCI](https://circleci.com/gh/cygnusb2b/radix.svg?style=svg&circle-token=ec28dde3a48ef1db08a8c87f8a97e8f5c6ec78fd)](https://circleci.com/gh/cygnusb2b/radix) [![CircleCI](https://img.shields.io/circleci/token/ec28dde3a48ef1db08a8c87f8a97e8f5c6ec78fd/project/github/cygnusb2b/radix.svg)](https://circleci.com/gh/cygnusb2b/radix)

Awesome sauce!!

Developing in Radix
-------------------------------
To get started, check out this repository and execute `docker-compose up` in the repository root folder. Your radix instance is now available via http://localhost:8700/manage/ (or whatever port you have configured).

To change the `APP` or other run-time environment variables, add the values into a `.env` file at the project root.
```sh
# ./env
APP=acbm:fcp
RADIX_APP_PORT=8700
RADIX_DB_PORT=8701
RADIX_REDIS_PORT=8702
RADIX_ES_PORT=8703

# OPTIONAL LOCAL SERVICES ENTRIES
RADIX_MONGO_HOST=mongo.platform.as3.io
RADIX_REDIS_HOST=redis.platform.as3.io
RADIX_ELASTIC_HOST=elastic.platform.as3.io
```

### Advanced Usage
By default, `docker-compose up` will start all services defined in the `docker-compose.yml` file. This will include all local service dependencies (redis, mongo, elastic), an apache server, and a script that will automatically perform a `composer install`, `app/console cache:warmup`, and a non-terminating `app/console assetic:watch` for the current `APP`. Additionally, this will spawn a script for the ember applicatio which will run `npm install`, `bower install`, and a non-terminating `ember build --watch`.

To execute a command within your environment use the following syntax:
```sh
docker exec radix_radix_1 php app/console COMMAND [ARGUMENTS...]
```

For an interactive shell, use the following:
```sh
docker exec -it radix_radix_1 /bin/bash
```

Your shell will start in `/var/www/html` -- the effective project root. You can access composer at `bin/composer` or symfony at `app/console`.

### Troubleshooting

Ensure that the `node_modules`, `bower_components`, and `tmp` folders are removed from your host machine's ember installation folder **before** starting the stack.

After running `docker-compose up`, you can use `CTRL+C` to gracefully stop the radix stack. If it does not respond, you can use `CTRL+C` again to kill the remaining stack elements.

In general, after stopping the stack (gracefully or otherwise), you should execute `docker-compose down` to perform cleanup actions.

As a last resort, you can execute `docker system prune` to clear all containers, images, and volumes -- which will essentially reset your docker environment.

### Requirements

The updated development requirements are:
- Docker >= 17.12.0
- Docker Compose >= 1.18.0

For detailed instructions for using docker, check out the `Docker & Kitematic` documentation on the [wiki](https://github.com/cygnusb2b/base-platform/wiki/Docker-&-Kitematic).

### Utilizing local database services
By default, local copies of redis, mongo, and elasticsearch are started when booting up the compose environment. You can access them directly by connecting the the relevant port.

To enable local services integration with platform, modify your `.env` file to include the `RADIX_*_HOST*` entries for the services you want to override. For example, setting `RADIX_MONGO_HOST=mongo.platform.as3.io` would force platform to connect to the local mongo service, rather than the shared cloud server.

#### Seeding & Accessing Data

You will likely want a local copy of the associated radix data. To get that, connect to your new local mongo instance and copy down the databases needed from production (or development).

```js
// Example for Firehouse
db.copyDatabase('radix', 'radix', 'mongo.platform.baseplatform.io')
db.copyDatabase('radix-cygnus-fhc', 'radix-cygnus-fhc', 'mongo.platform.baseplatform.io')
```

### Usage

A note on the documentation below: It assumes direct CLI entry on the host machine. Because the new Docker Compose environment uses a `.env` file which is passed to the underlying services, the recommended method of modifying the ATAG/`ACTIVE_CONTEXT` is to specify it in this file.

If accessing the terminal directly (see interactive shell above), you can still specify the `ACTIVE_CONTEXT` manually, though it is recommended to still use the `.env` file and restart the docker stack for consistency.

