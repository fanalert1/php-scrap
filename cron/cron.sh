cd /root/php_scrap
php extract.php
curl -X POST --data-urlencode 'payload={"channel": "#alerts", "username": "cronjob", "text": "Job completed fine"}' https://hooks.slack.com/services/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