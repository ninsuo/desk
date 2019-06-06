
# Server Installation

## Install Apache, MySQL & PHP


Be root.

```
sudo su root
```

Update PHP repositories:

```
sudo apt install apt-transport-https lsb-release ca-certificates
sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
sudo sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
sudo apt update
```

Install all requirements:

```
apt-get install php7.2 apache2 mysql-server php7.2-gd php7.2-mysql \
                php7.2-mbstring php7.2-intl php7.2-curl php7.2-xml php7.2-apcu php7.2-apcu-bc \
                php7.2-zip php7.2-xml
```

By going to http://desk.fuz.org/, you should see the Apache default page.


## Configure Apache for HTTP

Open `/etc/apache2/apache2.conf` and remove the default configuration:

```
<Directory /usr/share>
        AllowOverride None
    	Require all granted
</Directory>

<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
</Directory>
```

Create the `desk.fuz.org` directories.

```
mkdir -p /var/www/desk.fuz.org/sources
mkdir -p /var/www/desk.fuz.org/exposed
mkdir -p /var/www/desk.fuz.org/logs
echo 'Hello, world!' > /var/www/desk.fuz.org/exposed/index.html
chown -R www-data:www-data /var/www/desk.fuz.org
```

Create the `desk.fuz.org` http configuration.

In `/etc/apache2/sites-available/000-desk.conf`:

```
<Directory /var/www/desk.fuz.org/exposed>
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

<VirtualHost *:80>
    ServerName desk.fuz.org
    ServerAdmin desk@fuz.org
    DocumentRoot /var/www/desk.fuz.org/exposed
    ErrorLog /var/www/desk.fuz.org/logs/error.log
    CustomLog /var/www/desk.fuz.org/logs/access.log combined
</VirtualHost>
```

Remove default symlink and create the right one:

```
rm /etc/apache2/sites-enabled/000-default.conf
ln -s /etc/apache2/sites-available/000-prod-redcall.conf /etc/apache2/sites-enabled
```

Restart apache2:

```
service apache2 restart
```

Go to http://desk.fuz.org to check that you see "Hello, world!".

## Install Certbot

First, enable mod-ssl, mod-rewrite and mod-suexec on apache:

```
a2enmod ssl
a2enmod suexec
a2enmod rewrite
service apache2 restart
```

Then, install certbot:

```
apt-get install software-properties-common dirmngr certbot python-certbot-apache
```

Create the certificate for `desk.fuz.org`:

```
certbot certonly --agree-tos --email desk@fuz.org --webroot --webroot-path=/var/www/desk.fuz.org/exposed/ --domains desk.fuz.org
```

Reopen `/etc/apache2/sites-available/000-desk.conf` and replace it by:

```
<Directory /var/www/desk.fuz.org/exposed>
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

<VirtualHost *:80>
    ServerName desk.fuz.org
    ServerAdmin desk@fuz.org
    DocumentRoot /var/www/desk.fuz.org/exposed
    ErrorLog /var/www/desk.fuz.org/logs/error.log
    CustomLog /var/www/desk.fuz.org/logs/access.log combined
    FallbackResource /index.php

    # Redirect all traffic to HTTPS
    RewriteEngine on
    RewriteCond %{SERVER_NAME} =desk.fuz.org
    RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>

<VirtualHost *:443>
    ServerName desk.fuz.org
    ServerAdmin desk@fuz.org
    DocumentRoot /var/www/desk.fuz.org/exposed
    ErrorLog /var/www/desk.fuz.org/logs/error.log
    CustomLog /var/www/desk.fuz.org/logs/access.log combined

    SSLCertificateFile /etc/letsencrypt/live/desk.fuz.org/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/desk.fuz.org/privkey.pem
    #Include /etc/letsencrypt/options-ssl-apache.conf
</VirtualHost>
```

Restart apachee:

```
service apache2 restart
```

Go to https://desk.fuz.org to check that you see "Hello, world!" in a secure channel.

Add a crontab to automatically renew certificates:

```
# check certificates expiration once a day at 04:42
42 4 * * * certbot renew --agree-tos --quiet 2>&1 >/dev/null
```

## MySQL configuration

Run `mysql` as root in order to get into the server.

```
CREATE DATABASE desk;
GRANT ALL PRIVILEGES ON desk.* 
                     TO 'desk'@'127.0.0.1' 
                     IDENTIFIED BY 'some password';
EXIT                     
```

## Project installation

```
apt-get install git
exit # to not be root anymore
git clone https://github.com/ninsuo/desk.git
cd desk
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

Now, put the project configuration into file `.env.prod`

```
cd ..
vi dotenv
```

Complete, and put the following configuration:

```
###> symfony/framework-bundle ###
APP_ENV=prod
APP_SECRET=<generate some random secret>
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
DATABASE_URL=mysql://desk:<put same password as above>@127.0.0.1:3306/desk
###< doctrine/doctrine-bundle ###

###> symfony/swiftmailer-bundle ###
MAILER_URL=smtp://username:password@mail.provider.net:465?encryption=ssl
###< symfony/swiftmailer-bundle ###
```

Now create the following deploy script:

```
vi deploy.sh
```

```
#!/bin/sh

SOURCE=desk
TARGET=/var/www/desk.fuz.org/sources

cd $SOURCE

git fetch -p

git reset --hard origin/master

sudo rsync --info=progress2 --partial --recursive --delete --links --checksum ${SOURCE}/ ${TARGET}

sudo rm -rf ${TARGET}/app/cache/*

cd ..
sudo cp dotenv ${TARGET}/.env.prod
sudo cp dotenv ${TARGET}/.env.local

sudo chown -R www-data:www-data $TARGET

(cd $TARGET && sudo -u www-data php bin/console doctrine:migration:migrate)
```

Run it:

```
sh deploy.sh
```

Now clear up a bit directories:

```
cd /var/www/desk.fuz.org
sudo rm -rf exposed
sudo ln -s sudo ln -s sources/public exposed
```

Website https://desk.fuz.org should now be working.

## Crontabs

Some jobs require to be ran automatically every minute.

```bash
sudo crontab -e
```

Add the following line:

```
* * * * * sudo -u www-data /var/www/desk.fuz.org/sources/bin/console app:cancel-non-confirmed-bookings 60
```
