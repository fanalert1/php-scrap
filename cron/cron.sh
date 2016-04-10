cd ~/php_scrap/app
php tn_extract.php
php bms_extract.php
php tn_theater_extract.php
php bms_theatre.php
php notify.php
curl -X POST --data-urlencode 'payload={"channel": "#alerts", "username": "cronjob", "text": "Digi Job completed fine."}' https://hooks.slack.com/services/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


cd ~/mon
./monScript.sh
./mon_Alert_Slack.sh
