# Description of the software

## Installation

Tested on Debian Buster

### Notes

- `composer` uses git, therefore git needs to be available on the server. Alternatively, the `vendor/` directory from a local installation can be copied to the server. In this case, the `composer` installation steps can be skipped.
- If `unzip` or `7z` is not available on the server, the `php-zip` extension will be used (by `composer`) to unzip archived packages. If this leads to errors, try installing `unzip`. 
- The user executing `php-fpm` (probably `www-data`) needs write permissions to the `storage/` directory for logging and caching.

### Installation steps

1. Install required packages:
    ```
    apt update && apt install git nginx redis php7.3 php7.3-fpm php7.3-mbstring php7.3-dom php7.3-zip php-redis
    ```
2. Clone the repository (or copy to the server) into an accessible directory:
    ```
    git clone https://github.com/fahrgemeinschaft/FredEddisonMetaSearch /var/www/FredEddisonMetaSearch
    ```
3. Install composer: https://getcomposer.org/download/
4. Install dependencies:
    ```
    php composer.phar install
    ```
5. Create `.env` file and set `APP_DEBUG=false`:
    ```
    cp .env.example .env
    sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' .env
    ```
6. Generate app key:
    ```
    php artisan key:generate
    ```
7. Add nginx configuration (example, modify to your needs) in `/etc/nginx/sites-available/metasearch`:
    ```
    server {
        listen 8080;
        server_name FredEddisonMetaSearch;
        root /var/www/FredEddisonMetaSearch/public;
    
        add_header X-Frame-Options "SAMEORIGIN";
        add_header X-Content-Type-Options "nosniff";
    
        index index.php;
    
        charset utf-8;
    
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
    
        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }
    
        error_page 404 /index.php;
    
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }
    
        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
    ```
8. Enable configuration:
    ```
    ln -s /etc/nginx/sites-available/metasearch /etc/nginx/sites-enabled
    ```
9. Reload nginx:
    ```
    nginx -s reload
    ```
    or:
    ```
    systemctl restart nginx
    ```
10. Make sure `php-fpm` is running:
    ```
    cat /var/run/php/php7.3-fpm.sock
    ```
11. Start redis (if you want to use another port, make sure to edit `.env` accordingly):
    ```
    redis-server --port 6379
    ```
    or:
    ```
    systemctl start redis
    ```
12. Add BlaBlaCar and Bessermitfahren API keys in `.env`


## For local setup:
The software is written in php Laravel. For a local development environment, laradock.io is recommended.

### after installation: 

Start Laradoc: docker-compose up -d nginx redis workspace 

Into the .env (from laravel):

REDIS_CACHE_DB=0 
CACHE_PREFIX= 
CACHE_DRIVER=redis 

To get into the redis: docker-compose exec redis bash and then redis-cli 

### Test the environment:
access http://localhost/api/trip/search/ via POST and enter a search object:

#### Example search object 1:  
{"startPoint":{"location":{"latitude":52.5198535,"longitude":13.4385964}, "radius": "50.0"},"endPoint":{"location":{"latitude":53.5511,"longitude":9.9937}, "radius": "50.0"},"departure":{"time":"2020-08-16T11:06:04. 690Z","toleranceInDays":0},"arrival":null,"page":{"firstIndex":20,"page":0,"pageSize":20},"reoccurDays":null,"availabilityStarts":"2020-08-16T11:06:04. 690Z", "availabilityEnds":null, "tripTypes":[0], "transportTypes":[0], "animals":2, "baggage":1, "gender":2, "organizations":[], "smoking":3} 
 
#### Example Search Object 2:  
{"startPoint":{"location":{"latitude": 52.522,"longitude":13.411}, "radius": "50"},"endPoint":{"location":{"latitude":53.5511,"longitude": 9.9937}, "radius": "50"},"departure":{"time":"2020-08-14T11:06:04. 690Z","toleranceInDays":2},"arrival":null,"page":{"firstIndex":20,"page":0,"pageSize":20},"reoccurDays":null,"availabilityStarts":"2020-08-14T11:06:04. 690Z", "availabilityEnds":null, "tripTypes":[0], "transportTypes":[0], "animals":2, "baggage":1, "gender":2, "organizations":[], "smoking":3} 


