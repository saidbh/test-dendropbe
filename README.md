# Documentation API

Official dendromap Web services building with Symfony. To get further information symfony architecture go to :
> https://symfony.com/

Getting started

Install docker && docker-compose in your system depend on your system.
> https://www.docker.com/

Go to root dir and execute
> docker-compose -f docker/docker-compose.yml up -d

Install php dendancies :

> docker exec -it dendromap-php bash

Install all composer dependancies with composer

> composer install

Create database if it's not created
> php bin/console d:d:c

Update bdd schema
> php bin/console d:s:u --force

Change root user mysql Configure Database Enter to mysql container
> docker exec -it dendromap-api-mariadb bash

Connect to root mysql
> mysql -u root -p

Change Mysql user
> Create User Databases;
> CREATE USER 'nouveau_utilisateur'@'%' IDENTIFIED BY 'mot_de_passe';
> GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'%';
> FLUSH PRIVILEGES;

Exit the container and rebuild environement
> docker-compose -f docker/docker-compose.yml up -d

url Api documentation swagger serve on
> http://localhost:8001/api/doc

####   prod && pre-prod

1. Connect with ssh credentials
2. Pull the change from develop (preprod) or master (prod) repository
3. Make sure you clean cach.
