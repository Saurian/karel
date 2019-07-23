#!/bin/bash

printf "Cistim cache\n"
docker container prune -f
printf "Startuji Docker...\n"
docker-compose up --build