## Description

This program takes two coordinates - latitude and longitude, visits as much breweries
as it can and goes back to home location without travel distance exceeding 2000 km.
During the process prints every brewery visited, distance traveled, unused distance,
execution time and every beer type collected on the way.

# Docker -------------------------------------------

## Docker install

`wget -qO- https://get.docker.com/ | sh`

`sudo usermod -aG docker [your-user-name]`

## Docker compose install

`sudo -i`

`curl -L https://github.com/docker/compose/releases/download/1.8.0/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose`

`chmod +x /usr/local/bin/docker-compose`

## Docker setup

`cp docker-compose.yml.example docker-compose.yml` - Create config file from example

## Docker commands

`docker-compose up -d` - Starts your container in background

`docker-compose stop` - Stop your containers

`docker-compose logs [container-name]` - Show container log (app,web,php,mysql)

`docker-compose run [container-name] bash` - Logs into container (app,web,php,mysql)

`docker exec -i -t [container-name or id] bash`  - Logs into container (app,web,php,mysql)

`docker-compose ps` - Show active containers processes 

## Setup

Copy config file, edit database settings.

    cp config.ini.example config.ini

## Migration

Run migration file which creates database tables and fills them from resources folder.

    php migration.php

## Run

Run program with two settings - latitude and longitude of home location. Example:

    php run.php 51.355468 11.100790
