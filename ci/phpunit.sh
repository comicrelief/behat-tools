#!/usr/bin/env bash

set -e

[ -d behat-tools ] && cd behat-tools
[ -f ~/.bashrc ] && . ~/.bashrc

mkdir ~/.ssh
touch ~/.ssh/known_hosts
ssh-keyscan github.com >> ~/.ssh/known_hosts

composer install
composer test

true
