# 雙連教會 - 後端

## 基本佈署

此專案使用 [Laravel](https://laravel.com/) 框架來開發，
以下簡述佈署時需要的必要元件，更多細節可參考 [Laravel 官方文件](https://laravel.com/docs/9.x)。

### PHP

此專案使用 Laravel 9，執行環境需安裝 PHP 8.0 以上，
PHP 環境需要的相關軟體也都需要一併安裝，包含 php-cgi、php-fpm 等等。

### Composer Install

[Composer](https://getcomposer.org/) 是 PHP 環境的套件管理工具，可使用以下指令安裝 Composer:

```bash
$ sudo curl -s https://getcomposer.org/installer | sudo php
$ sudo mv composer.phar /usr/local/bin/composer
```

並以 Composer 安裝依賴套件:

```bash
$ cd /path-of-this-project/
$ composer install
```

### Cloud Service 

- Database

    資料庫使用 MySQL，並使用 Azure 雲端平台的 MySQL 服務。
- Storage

    檔案儲存使用 Azure 雲端平台的雲端儲存空間。

### .env

複製 [.env.example](.env.example) 為 `.env` 並設置其中的必要欄位，包含 DB、Storage、Key 等等。

###  Reverse Proxy

建議使用 [NGINX](https://www.nginx.com/) 作為伺服器端的反向代理，並把指定的網域名導向專案所在路徑的 `/public` 資料夾。

當自行購買 SSL 憑證時，需要把 SSL 放至指定路徑，並在 Reverse  Proxy 設定檔中定義 SSL 的存放路徑，以啟用 `https`。

### Scheduled Job (Cron)

指定時間跟頻率執行的程式稱為 Cron job，可參考 [Laravel 官方文件 - Task Scheduling](https://laravel.com/docs/9.x/scheduling#running-the-scheduler) 的說明，在 Server 加入 Cron job:

```
# /etc/cron.d/laravel_schedule_run
* * * * * www-data /usr/bin/php /path-of-this-project/artisan schedule:run >> /dev/null 2>&1
```

### Queue job

執行時間較長、但不需即時的任務會放入 Queue 內，等待被依序執行，確保 API 不會很容易被這些任務佔滿，

可使用 [Supervisord](http://supervisord.org/) 或 [systemd](https://systemd.io/) 來作為 Laravel Queue 的執行軟體，
如果使用 Supervisord，可先參考此設定檔，再進一步做最佳化調整:

```
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-of-this-project/artisan queue:work --sleep=3 --tries=1 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=2
redirect_stderr=true
stdout_logfile=/path-of-this-project/storage/logs/laravel.log
stopwaitsecs=3600
```

### CICD

CICD 自動化佈署流程使用了 [Github Action](https://github.com/features/actions)，詳細定義可參考 [deploy-production.yml](.github/workflows/deploy-production.yml)。

