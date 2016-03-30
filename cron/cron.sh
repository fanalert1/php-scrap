cd ~/php_scrap/app
#php bms_extract.php
#php tn_extract.php
#php notify.php
curl -X POST --data-urlencode 'payload={"channel": "#alerts", "username": "cronjob", "text": "Digi Job completed fine."}' https://hooks.slack.com/services/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