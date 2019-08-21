#!/bin/bash
echo "Cistim cache"
docker container prune -f
echo "Startuji Projekt..."
docker-compose up --build --force-recreate
