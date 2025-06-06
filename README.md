# tinyPHPshortener
A tiny PHP based URL shortener with a flat file structure and basic authentication.

![250606-1002-001](https://github.com/user-attachments/assets/9ea653bd-1365-4c2b-ab54-c50d5dc3a762)

![250606-0957-001](https://github.com/user-attachments/assets/cd13fedf-9652-464c-be81-92e46a3c0c9a)


## Setup

Make sure the **.storage** path is writeable by your webserver.

For Nginx here is my example server config:

```nginx
## Short Link redirector
server {
    server_name go.mydomain.com;
    root /var/www/shortener/htdocs;
    location /new {
        rewrite ^/new$ /index.php last;
    }
    location = / {
        return 302 https://mydomain.com;
    }
    try_files $uri $uri/ /openurl.php?su=$uri;
    include snippets/default_vhost.conf;
    listen [::]:443 ssl http2; # managed by Certbot
    listen 443 ssl http2; # managed by Certbot
    # ... other lets encrypt stuff ...
}
```

In the `.storage` subdir you find a config.example.php copy it to config.php and edit as needed.


### Cleanup and Maintenance

Currently the maint script is not done yet. So I temporary use a cronjob to clean very old stuff from the .storage directory.


```bash
# cleanup short URL stuff
50 4 * * * find /var/www/shortener/htdocs/.storage -type f -name "*.json" -mtime +365 -delete
52 4 * * * find /var/www/shortener/htdocs/.storage -type d -empty -delete
```



