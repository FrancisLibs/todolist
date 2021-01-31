# Projet8: TodoList

This site was created in order to study the Symfony framework (V5.1), as part of an application developer training. The subject is à todo list.
The main objective of this training is to learn about web application development with the Symfony framework and how it is possible to improve an existing application. The work also consists of producing documentation explaining how the authentication of users of the application works.

Environnement
-WampServer 3.2.3.0
-Apache 2.4.41
-PHP 7.4.6
-MySQL 8.0.18
-Composer 1.10.8
-Git 2.27
-Symfony 5.1
-JQuery 3.4.1
-Bootstrap 4.4.1

Installation

Environnement
The installation of an apache environment, with min. PHP 7.4 is necessary to work with Symfony 5.1
Notice: Several extensions of PHP must be activated.

Composer is needed to install Symfony and his components. [https://getcomposer.org/]

Git facilitate the download from GitHub by your system. [https://git-scm.com/downloads]

Files deployments
It Is possible to use 2 different methods:

- By “hand”: copy the entire repository from GitHub to your pc repository.
  Repository by GitHub address: [https://github.com/FrancisLibs/todolist.git]

- Or (easier) clone the repository from GitHub by your Pc with a git command (it need to install first Git): git clone [https://github.com/FrancisLibs/snowtricks.git]
  After installing the files, it is necessary to install the dependencies. Use the composer command:
  composer install.

Database
To inform Symfony what’s the database name and other accessing information, so the connection name and password, the file .env, in the root directory of the project, is to be modified: Looking for the line that begin with DATABASE and give the right information to access on your database.
For example:
DATABASE_URL=mysql://root:password@127.0.0.1:3306/todolist?serverVersion=8.0.18
Where “root” is the connection name, “password” is the connection password (It can be blank) and “todolist” is the database name.

If you encounter some problems, feel free to visit the doctrine site: [https://symfony.com/doc/5.1/doctrine.html]

After them, you need to create the database:
php bin/console doctrine:database:create

Open a new console, and create it with 2 commands:

- php bin/console doctrine:migrations:diff
- php bin/console doctrine:migrations:migrate

If the first command not works, verify if the php -ver command is functional and show the version of php. If the command not work, be sure that the system variable PATH, contain the php.exe route.

At the end of the procedure, use the fixtures to load fake data to the data base.
To load this first data, use this command:
php bin/console doctrine:fixtures:load

Finish: run the application
The Apache/Php runtime environment must be start by using the command:
php bin/console server:run
The URL [http://localhost:8000] is the address who’s listen the symphony website.

By a virtualhost
If you don't want to use WebServerBundle, you can use your Wamp (or other) environment in a normal way.
This by configuring a virtual host.
Then check [http://localhost]

Users accounts
Several users have been created when creating the fake data.
Under other names, they are:
Admin, essai, anonyme, and many others.
All with the same password: “password”
