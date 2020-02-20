# Sortir.com

## Getting started

```bash
## Install
git clone https://github.com/maxime-boussin/projet-symfony.git
cd projet-symfony
composer install

## Runs the embeded server
php -S localhost:8000 -t public
````
###Initialize excursions purge
```bash
crontab -l | { cat; echo "0 1 * * * php /mnt/appli/sortir-web/bin/console app:excursions:purge"; } | crontab -
```