#!/bin/bash
/home/vladimir/Documentos/Codigo/flor360.com/sync.txt.sh
#. beta.sync.sh
rsync --compress-level=9 --exclude '*.psd' --exclude '*.xcf' --exclude '*.git' -a --progress /home/vladimir/Documentos/Codigo/flor360.com/  root@173.255.192.4:/var/www/flor360.com/
