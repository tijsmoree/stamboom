# Stamboom
An app to save your family tree.

## Set-up development environment
To develop, you need [docker](https://www.docker.com/). Running `docker-compose up` gives the site on `http://localhost` and you can access the raw database via phpmyadmin on `http://localhost:8080`. You need to keep docker running to develop. It will update automatically on altering the code.

Updating to the latest version of the database structure is done using:
* `docker exec -it stamboom_php_1 sh -c "php server/yii migrate"` and select `yes`

There is one set of credentials for the website:
* E-mailadres: `root@stamboom.nl`
* Wachtwoord: `verysecret`

To login to phpmyadmin, you can use:
* Server: <i>leave empty</i>
* Username: `root`
* Password: `verysecret`

## Set-up on the server
To set the site up on a server you have to have a `mysql` running and fill in the log-in details of this service into two files that have to be created in `/server/config`.
* `db.php` that looks like `db.php.default` with log-in credentials on a database of a user that can select, update and delete on the appropriate database
* `db_admin.php` that looks like `db_admin.php.default` with log-in credentials to create and delete tables in that database as well

Then the following commands have to be executed:
* `git pull`
* `cd client`
* `npm install`
* `ng build --prod`
* `cd ../server`
* `composer install`
* `php yii migrate --interactive=0`

### Configure web server (using Apache2)
* Point the DocumentRoot of your site to the `/client/dist/Stamboom` subfolder of this repository
* Make an Alias "/api" to the `/server/web` subfolder of this repository

## Contributors
* Tijs Moree
