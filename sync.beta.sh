#!/bin/bash
rsync --compress-level=9 --exclude '*.psd' --exclude '*.xcf' --exclude '*.git' -a --progress ./  root@173.255.192.4:/var/www/flor360.com/beta/
