## Description

This program takes two coordinates - latitude and longitude, visits as much breweries
as it can and goes back to home location without travel distance exceeding 2000 km.
During the process prints every brewery visited, distance traveled, unused distance,
execution time and every beer type collected on the way.

## Setup

Copy config file, edit database settings.

    cp config.ini.example config.ini

## Migration

Run migration file which creates database tables and fills them from resources folder.

    php migration.php

## Run

Run program with two settings - latitude and longitude of home location. Example:

    php run.php 51.355468 11.100790
