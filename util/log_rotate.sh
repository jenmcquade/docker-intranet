#! /bin/bash

timestamp() {
  date +"%Y-%m-%d_%H-%M-%S"
}

sudo tar -zcvf ./backups/logs_$(timestamp).tar.gz logs
sudo rm -rf ./logs/*
