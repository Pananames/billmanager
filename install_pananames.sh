#!/bin/sh

yum -y install php php-mysqli
wget https://codeload.github.com/Pananames/billmanager/zip/masterv
tar -xvf master --strip-components=1 -C /usr/local/mgr5/
chmod +x /usr/local/mgr5/processing/pmpananames.php
rm /usr/local/mgr5/README.md
rm /tmp/master
pkill core