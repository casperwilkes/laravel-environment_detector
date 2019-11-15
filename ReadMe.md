# Laravel Environment Detector #

This is a simple environment detector made for working with multiple environments. This package should not need to be run very often, if at all
after it's initial setup.

This is great for multiple environment setups. 

Sometimes you don't want to save your `.env` files to your repository, in that case, add `.env*` to your `.gitignore` file.

## Installation ##

There are a couple steps necessary to get the environment detector up and running.


### Composer ###
To install the package through composer:

```
composer require casperwilkes/laravel-environment_detector
```

### Laravel Publish ###

Once you have installed through composer, you will want to publish the assets of the project

```
php artisan vendor:publish --tag=env-detector
```

This will publish the necessary bootstrap files and the config file.

The bootstrap file will be located at `./bootstrap/environment_detector.php`.

The config will be at `./config/environment_detector.php`

### Check Config ###

After you've published your config file, you'll want to update it with your environments, and perspective short names. 

By default, there are some environments already setup.

```php
'environments' => [
        'local' => 'local',
        'dev' => 'localhost',
        'qa' => '',
        'stage' => '',
        'prod' => '',
    ],
```

You can use whatever short names you want to describe your environment stage. This will create the shortname for the `.env` files.

For the environment name, you'll want the machines hostname. You can run `hostname` from the command line to get it. 

For example, if you had 2 production environments called `prod_one`, and `prod_two`, you would use those for the short name, and use the machine's 
hostname for the environment field.

```php
'environments' => [
        'local' => 'local',
        'dev' => 'localhost',
        'prod_one' => 'box284.gatorhost.com',
        'prod_two' => 'box246.redhost.com'
    ],
``` 

### Package Publish ###

After you've published the vendor assets, you will want to publish the package. 

To publish the package, run:

```
php artisan envdetector:publish
```

This will create the necessary environment files, and update your `App.php` to require the `environment_detector`. A backup of your previous version
of `App.php` will be backed up in the same directory.

This will work it's way through your `environment_detector` config, and create a `.env` file for each environment found.

So, for our previous example, we'll get 4 different `.env` files. 

* .env.local
* .env.dev
* .env.prod_one
* .env.prod_two

### Remove Package ###

If for whatever reason you wish to remove the package, you can un-publish the package.

Run 
```
php artisan envdetector:unpublish
```

This will remove the bootstrap files, the new config files, and restore your app from the backup process. 

**note:** If no backup is found, it will attempt to remove the require statement from `App.php`. 

## Usage ##

There are a couple usage options for both the envdetector commands. 

### Publish ###

For the publish command, there are 3 options:

* bootstrap (-b|--bootstrap)
    * this will backup `App.php` and add the require statement to bootstrap to load the correct `.env` file based on config.
* configs (-c|--configs)
    * This will create the necessary `.env.*` config files based on the config file. They will be copied from the original `.env` file.
* all (-a|--all), this will run all other options

**Notes**

For the bootstrap option, a backup copy of the original `App.php` is created and stored in the same directory. This will be used in case you've made
previous changes to `App.php`, and you wish to revert back.

For the configs option, it will detect if a particular config already exists, and whether it should be overwritten. A prompt will appear asking
for permission before assuming. If not configs are already setup, it will go ahead and create them. You can choose to overwrite all, or only some.

This is useful for when you've only added 1 or 2 new environments.

The original `.env.example` and `.env` will always remain untouched by this package.
    
### Un-publish ###

For the un-publish command, there are 3 options:

* bootstrap (-b|--bootstrap)
    * This will restore the `App.php` file to it's original contents.
* configs (-c|--configs)
    * This will remove the config file, and all package created `.env.*` files.
* all (-a|--all), this will run all other options 

