# Messaging project

## Requirements
* Vagrant 
* Virtualbox / VMWare / Parallels

## installation
### messaging project setup
* clone the repository `git clone https://github.com/XDF05/secured-messaging.git`

* move into the cloned repository `cd secured-messagingt/messaging-project`

* add required packages in order to run the project `composer require laravel/breeze --dev`

* copy the example .env file `cp .env.example .env`

* create a database and modify the .env file with the database information
    ```
    homestead default user: homestead
    homestead default user password: secret
    ```
* generate an application key in order to encrypt stuff `php artisan key:generate`


### Homestead setup (Web server)
* Official documentation : https://laravel.com/docs/8.x/homestead
* install the homestead vm `vagrant box add laravel/homestead`

* Install homestead inside the __~/Homestead__ folder `git clone https://github.com/laravel/homestead.git <yourdirectory>/Homestead`

* move into the homestead folder and initialise homestead 
    ```
    cd <yourdirectory>/Homestead
    bash init.sh
    ```
* configure the Homstead.yaml to your liking but make sure to add the following line:
    ```
    ssl: true
    ```

* setup the folder mapping to the location of the cloned messaging-project directory
    ```
    folders:
    - map: <your-directory>/secured-messaging/messaging-project
      to: /home/vagrant/code/messaging-project
    ```
* setup the site url to __messaging-project.test__
    ```
    sites:
    - map: messaging-project.test
      to: /home/vagrant/code/messaging-project/public
    ```
* generate a SSH keypair if not already done (ssh -keygen)
    ```
    ssh-keygen -t rsa
    ```

### Start the server
    cd <yourdirectory>/Homestead/
    vagrant up

ssh into the homestead VM and copy generated certificates into messaging-project/storage/app/ 
    ```
    cd <yourdirectory>/Homestead/

    vagrant ssh

    cp /etc/ssl/certs/ca.homestead.messaging-project.crt ~/code/messaging-project/storage/app/
    ```

### setup the Database inside the server
    ```
    cd ~/code/messaging-project
    
    php artisan migrate
    ```
 #### !! don't forget to add the certificate located in messaging-project/storage/app as a trusted certificate in your browser !!

*__Homestead.yaml file example is provided in the messaging project setup__





## Credits
Project made at HE2B ESI
