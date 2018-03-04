# MEMEBoard

MEMEBoard is an example of a multimedia board written using PHP and Laravel 5.
I created this project while starting learning more about Laravel and the MVC pattern.
Note that this is just an academic project, for this reason it provides just some very basic features (such as MEME creation, comments, and user management).

# Installation and usage

I developed this project using the awesome [Homestead VM](https://app.vagrantup.com/laravel/boxes/homestead) for Vagrant, so you can easily download and run this project within Homestead using Composer or manually download it from the repo on GitHub.
If your are going to install the project and its dependencies through Composer, you just need to run this command:

`composer require ryanj93/memeboard`

Once the project has been downloaded you may want to edit configuration in ".env" that may vary according to the settings of your VM.
Before running the project make sure to issue the command "php artisan install" to set up the project (database tables, requirements check up, and so on...), if you want to create some fake data while running the install command, just add the "--seed" option to "php artisan install", it will spawn 5 admin users, all the categories supported by [LoremPixel](http://lorempixel.com/) and 30 MEMEs containing images from [LoremPixel](http://lorempixel.com/).
To run the project you could use the PHP's built-in web server or NGINX (shipped with Homestead).

This application needs basically these three dependencies (installed automatically by Composer):

* Laravel 5.5 (laravel/laravel) ([Packagist](https://packagist.org/packages/laravel/laravel))
* PHP e-mail address validator ([Packagist](https://packagist.org/packages/ryanj93/php-email-address-validator))
* PHP password toolbox ([Packagist](https://packagist.org/packages/ryanj93/php-password-toolbox))

It requires PHP 7 or greater, in addition to this, imagick is required (in order to handle images and GIFs).
If your are going to allow video uploads, note that ffmpeg is required in order to resize uploaded videos, this project deals with ffmpeg using the function "shell_exec", which shall be enabled (usually is disabled due to security reasons), to install ffmpeg on your VM just run "apt install ffmpeg".
Consider that GIF and video processing is expensive and can degrade a lot your server performances.
Parameters like texts, title, domain and so on can be edited in ".env", icons are stored in "public/icons", they have been generated using [Favicon Generator](https://www.favicon-generator.org/), you could use this website to generate your own icons and replace the folder with the zip archive dowloaded from this tool.
To change contents in the "about" page you need to edit the HTML page itself.
After configuration and install you may want to create your own admin user, to achieve this you have to register on the application, then run this query in order to grant admin rights to yourself:

`UPDATE users SET admin = 1 WHERE id = [YOUR USER ID];`

# Additional resources

If you want to see some screenshot of the application without or before installing it you can check the [project's page on Behance]().
You can find a PSD and a PNG export of the main logo in the directory "resources".

# License

You are free to edit, redistribute and use this project for both commercial and non commercial purposes, backlinks are not required but appreciated.
If you're going to add a backlink to the author, make sure to point it to my [personal website](https://www.enricosola.com).
If you think that this project is useful please run "composer thanks" to spread a star to this and all dependencies' repos 😉.