# Installation

To install Pananames module on your Billmanager:
## Automatically installation
If you have CentoOS 7 on your server, just execute command

```bash
curl -s https://raw.githubusercontent.com/Pananames/billmanager/master/install_pananames.sh | bash -s
```
## Manually installation
Billmanager Pananames module require:
- php 5.4
- php-mysqli

To install, download zip archive with code and unzip files to:
- /usr/local/mgr5/processing/pmpananames.php
- /usr/local/mgr5/etc/xml/billmgr_mod_pmpananames.xml
- /usr/local/mgr5/include/php/pananames_commands.php
- /usr/local/mgr5/include/php/pananames_helper.php

Make file /usr/local/mgr5/processing/pmpananames.php executable
- chmod +x /usr/local/mgr5/processing/pmpananames.php

Reload Billmanager
- pkill core
