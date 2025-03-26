# ZooApp

This is the beginning of the ZooApp for COSC 3380. The mysql database has been deployed on azure so everyone is able to use it. We are now setting up the database and the github to get started for development.

Resources we are using: HTML, CSS, PHP, Mysql, and Azure.

Setup the structure of the files and created an .gitignore file to hide the database credentials. To connect to the database, we use composer which will need to be downloaded.

Windows: https://getcomposer.org/download/

Mac: 

1. Download Composer installer
    curl -sS https://getcomposer.org/installer | php

2. Move composer.phar to global directory
    move composer.phar C:\bin\composer

3. Verify Installation
    composer --version

4. In terminal of Project Directory
    composer install

Also setup the database.php file to connect to the database so other files can refer to database.php to access all tables.
