# Nexxus Stock Keeping

Nexxus stock keeping is a webapplication made for purchase, sale and keeping of any stock. The application is built upon most popular technologies, such as PHP, Symfony, MySQL, jQuery and Bootstrap. This repository holds version 2 of Nexxus, which is made open source under the terms of the GNU General Public License (GNU GPLv3). 

## Background

My name is Mardten de Bakker and I am owner of Copiatek. Copiatek refurbishes and recycles used computer hardware and uses Nexxus for its own daily workflow. It started when I was manager in a thrift store and I wanted to sell used computers. There was no way to keep track of the computers and to know in what state they were in. Since we operated multiple stores, I also needed to keep track of the computers at several locations and which ones were sold.

The software evolved and it became possible to also keep track of other objects besides computers. Also a billing part was introduced so we could use it as a point of sales. Since we picked up objects through the country, I needed a way to keep track of logistics and distribution. Most of the whishes have been implemented, however it was build on a shady foundation. Last year we decided to start developing from the ground up. This repository tracks the development of the second version of Nexxus Stock Keeping.

## Requirements

The following technologies should be present in order to install and use Nexxus.

- Web server like Apache on Linux (Tested with Apache 2.4.7 and Ubuntu Linux 14.04.4)
- PHP 7 with regular extensions (tested with 7.0.32)
- MySQL 5.6 (Tested with 5.6.33-0ubuntu0.14.04.1)
- Composer (dependency manager for PHP)
- Git

Nexxus is tested with these versions. Higher versions of PHP could give undesirable results.

## Installation

In order to install Nexxus, you need to open a Windows command prompt, Linux terminal or SSH connection (like with Putty). Some steps below might require more user rights, ie. when you get permission denied errors. In that case prepend the command with the _sudo_ command. We advise not to install the Nexxus application using the _root_ user.

If this manual is too brief for you, more than enough information can be found on the internet, as these steps are very regular for a Symfony 3 application.

For installation follow these steps:

### 1. Choose the right place to install

In the terminal, go to the right place where you would like to install Nexxus. Typically this is one directory above the _public_html_ or _wwwroot_ folder. **Never install Nexxus inside the public web folder!** Like 90% of the source code of a webapplication like this is not in the public web folder. Step 3 of this installation manual will make a certain part of the application available for public.

### 2. Download the repository

To clone the source code, run:
```
git clone https://github.com/Nexxus/NSK.git nexxus
```
This will download all source code and place it in a newly created folder named _nexxus_. Go to the new folder when the download has finished:
```
cd nexxus
```
All next steps will happen in this new folder.

When using Linux, make sure the application can modify the _var_ folder:
```
chmod -R 777 var
```
Now you have your clean clone of Nexxus.

### 3. Move some files to the public web folder.

If you need a production web server like Apache, the public web folder is at a designated location, in which you (fortunately) did not install Nexxus. To make Nexxus publically accessible, move the web folder of the installation to the public web folder of your server. The public web folder is typically named _public_html_ or _wwwroot_. This is an example how it can be achieved:
```
mkdir ../public_html/nsk
mv web/* web/.* ../public_html/nsk
```
Don't copy this line literally, but apply it to the situation of your web server. In this example, Nexxus will be available on URL http://www.yourdomain.com/nsk

When you would get an error about device or resource being busy, there is no problem. Just execute this extra command:
```
rm -rf web
```
In _composer.json_ you should add or change the value for field _symfony-web-dir_. The file is in the current directory. You can change this file with a text editor like Nano. The line to change is somewhere at the bottom in the node named _extra_. In this example, the value should be changed from _web_ to _../public_html/nsk_.

### 4. Install dependencies

Nexxus depends on other PHP packages. To install these, run:
```
composer install
```
This will take a while...

After installation, some parameters will be asked. You can enter thru most of them. The database does not need to exist yet, but the database user does need to exist. For _database_user_ and _database_password_ you need to enter the appropiate values. For the _secret_ enter a random value of your liking. These parameters can be edited later in file _./app/config/parameters.yml_.

### 5. Install the database

To create a database with all needed tables, run:
```
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
```

### 6. Create the first user

To get you started, make the first user in the terminal
```
php bin/console fos:user:create superadmin superadmin@whatever.com p@ssw0rd
php bin/console fos:user:promote superadmin ROLE_SUPER_ADMIN
```
Don't copy these line literally, but change the name, email and password to your likings. Take care of choosing a good password.

Now you can login and create other users in the Admin section.

### 7. Finishing installation

Finally make sure the application root can find the source code from its new location. This happens in a different folder:
```
cd ../public_html/nsk
```
You are now in the public root. Open _app.php_ in a text editor and change lines 6 and 7 like so:
```
$loader = require __DIR__.'/../../nexxus/app/autoload.php';
include_once __DIR__.'/../../nexxus/var/bootstrap.php.cache';
```
Finally, if you are installing an production environment, remove _app_dev.php_. In Linux:
```
rm app_dev.php
```
Please check if the commands are appropiate for your specific situation. For example, you might have installed the application in different folders then _nexxus_ and _nsk_.

### 8. Use Nexxus

Congratulations, Nexxus is now ready for use. To start with, go to the Admin section and configure all meta data: Users, Locations, Product types, Attributes, Product statuses, Order statuses, Tasks. Without  this meta data the application will not behave as desired.

When your first visit to Nexxus gives an error 500, our first guess is that the write rights to the _var_ folder is not accurate. You could try to repeat this command from the application folder:
```
chmod -R 777 var
```
For more information, for example about upgrading installations or the API reference, go to the [Wiki](https://github.com/Nexxus/NSK/wiki).

