# Recalboy

[![Total Downloads](https://poser.pugx.org/laravel/lumen-framework/d/total.svg)](https://packagist.org/packages/laravel/lumen-framework)


Recalboy is your "1-click" companion for [Recalbox](http://www.recalbox.com) and is powered by the micro-framework [Lumen](http://lumen.laravel.com).

I made it for a personal project, to provide a (very) little second screen for an arcade cabinet, to get access to main features of Retroarch in a breeze. 

* Save
* Load last save state
* Take a screenshot
* Open/Close Retroarch menu
* Reset game
* Exit game

**DEMO**: you can watch a demo of an early version [here](<https://youtu.be/k_k3ho4qGwg>)

## Requirements

You must have a local server (Raspbian, or any Unix operating system) with Apache and PHP 5.6.4 minimum, and Composer.
My installation is done on a Raspberry Pi Zero. In this case, I suggest you to temporary increase swap memory to avoid any memory issue during installation. [Read more](http://raspberrypi.stackexchange.com/questions/70/how-to-set-up-swap-space).

Other requirements:
* Curl (php5-curl)
* Memcached (optional)

You can use this [nice tutorial](https://raspbian-france.fr/installer-serveur-web-raspberry/) (in french) to install Apache and PHP.

Wamp users: not sure if Recalboy will work, as there's one symbolic link and Windows doesn't handle them.
Virtual Machine users : I suggest you to configure your network on Bridge Access, so that your VM will get its own local IP.

## Setup Apache

I suggest you to install Recalboy as default website for Apache, if you're not comfortable with web hosting.

* Edit `/etc/apache2/sites-enabled/000-default.conf` this way:

```bash
<VirtualHost *:80>
        # The ServerName directive sets the request scheme, hostname and port that
        # the server uses to identify itself. This is used when creating
        # redirection URLs. In the context of virtual hosts, the ServerName
        # specifies what hostname must appear in the request's Host: header to
        # match this virtual host. For the default virtual host (this file) this
        # value is not decisive as it is used as a last resort host regardless.
        # However, you must set it for any further virtual host explicitly.
        ServerName recalboy.local

        ServerAdmin your@email.com
        DocumentRoot /var/www/html/public

        <Directory "/var/www/html/public">
                AllowOverride All
                Order allow,deny
                Allow from all
                Options FollowSymLinks
        </Directory>

        # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
        # error, crit, alert, emerg.
        # It is also possible to configure the loglevel for particular
        # modules, e.g.
        #LogLevel info ssl:warn

        ErrorLog ${APACHE_LOG_DIR}/recalboy.error.log
        CustomLog ${APACHE_LOG_DIR}/recalboy.access.log combined

        # For most configuration files from conf-available/, which are
        # enabled or disabled at a global level, it is possible to
        # include a line for only one particular virtual host. For example the
        # following line enables the CGI configuration for this host only
        # after it has been globally disabled with "a2disconf".
        #Include conf-available/serve-cgi-bin.conf
</VirtualHost>
```
* Restart Apache `sudo service apache2 restart`
* Activate url rewriting if necessary `sudo a2enmod rewrite`.


## Installation

Well, this process is not tested, but it should work :')

* Get [Composer](https://getcomposer.org/download/)
* Optional : move it to /usr/local/bin (to use it as "composer" form anywhere in your machine)
* Go to `/var/www/html` (or anywhere else where you want to install it)
* Require Recalboy with `composer require kjbstar/recalboy`
* Update dependencies with `composer update`
* `storage` folder must have write access: `sudo chmod -R 777 /path/to/your/recalboy/install/storage`

Bravo ! Recalboy is now installed, you should be able to access it on you server's local IP, or with the `ServerName` value you have set in `000-default.conf`, if you modify the `hosts` file of your client machine. But access by IP is fine :)


## Configuration

Now setup your Recalbox IP in `.env` file, line 15.
That's it ! The most common values of a classic Recalbox installation are already set.

### IMPORTANT
You **MUST** activate network commands in Retroarch to make Recalboy working with your Recalbox.
To do so, enable network commands in the Retroarch settings menu, or ensure that network_cmd_enable = "true" is set in retroarch.cfg.

### REQUIRED CONFIGURATION TO SET IN .env file
#### Recalbox configuration : IP, SSH login and password
`RECALBOX_IP` - set here your Recalbox local IP.

`RECALBOX_LOGIN` and `RECALBOX_LOGIN` are already set with default Recalbox values for SSH access.

### OPTIONAL CONFIGURATION FROM .env FILE
#### Recalboy can check by himself if a game is running, or you can click on Recalboy logo to check by yourself
`REFRESH_AUTO` - Available values: `1` or `0` - Default: `1` (enabled)

`REFRESH_DELAY` - Default: `10000` (check every 10 seconds)

`RECALBOX_ROMS_PATH` - Default: `/recalbox/share/roms`

`UPLOAD_SCREENSHOTS` - Default: `0` (disabled, set `1` to enable)

`UPLOAD_METHOD` - Set here your favorite storage solution to upload automatically your screenshots. Available methods: `Amazon S3`, `Google Drive`, `Dropbox` (64-bits architecture only, not fully tested).

`RECALBOX_SCREENSHOTS_PATH` - Default: `/recalbox/share/screenshots` It's screenshots path on Recalbox side !

`REMOTE_PATH` - Where you will upload your screenshots on your remote storage solution.

You then have options to set the keys of your storage solution.


## TODO
* Improve the layout when I'll get my PiTFT screen :)
* Maybe a templating system to easily choose another layout
* Add more informations from running games, to get something less basic.
* Arcade games : finding, adding, and managing the display of instructions card / move lists, to never forget how to make a Hadoken :)

## Limitations
Sadly, as Recalboy use Retroarch Network Commands, keep in mind that the following systems are (still) not supported :
* Dosbox
* Pifba
* Kodi (obviously!)
* LinApple
* Moonlight
* Mupen64 (N64)
* PPSSPP (PSP)
* Reicast (Dreamcast)
* Scummvm
* Vice (Commodore)
* Dolphin
* AdvanceMame

## License
Well, Recalboy is open source. Feel free to fork it and improve it. Keep in mind I don't consider myself as a developer, so don't be too rough with my code :)
