[![Twitter Follow](https://img.shields.io/twitter/follow/cleancodestudio.svg?style=social)](https://twitter.com/cleancodestudio) 

# Slim Framework 4 Skeleton Application


Use this skeleton application to quickly setup and start working on a new Slim Framework 4 application. This application uses the latest Slim 4 with Slim PSR-7 implementation and PHP-DI container implementation. It also uses the Monolog logger.

This skeleton application was built for Composer. This makes setting up a new Slim Framework application quick and easy.

## Install the Application

Run this command from the directory in which you want to install your new Slim Framework application.

```bash
composer create-project slim/slim-skeleton [my-app-name]
```

Replace `[my-app-name]` with the desired directory name for your new application. You'll want to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` is web writable.

###About ChatApp

ChatApp (sounds like shut up, but it is a chat application, what a twist!) is a chat application that people can simply register with a nickname, an email and a password to connect others.

People can: 
* chat in private or in public groups
* create new groups and invite people there
* delegate their group ownership
* see the new messages sent to them
* edit/delete their messages
* set status for their account or groups to express themselves
* find new users and groups to connect

This is a backend application that uses a simple RESTful JSON API over HTTP(s).

To run the application in development, you can run these commands 

```bash
cd [my-app-name]
php -S localhost:8091 public/index.php
```
After that, open `http://localhost:8091` in your browser, or you can use Postman to test the API methods.

* You can see the available methods by sending a `GET` request to `http://localhost:8091`
* Controllers, Models and Helpers are located under `src` folder
* Configuration, Routing, and other setup files are located under `app` folder
* The database connection parameters: `app/settings.php`

---
Thanks!\
Gorkem Genc
