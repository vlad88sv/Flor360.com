#!/bin/bash
rsync --compress-level=9 -v root@vps.mupi.com.sv:/var/www/flor360.com/IMG/i/* ./IMG/i
