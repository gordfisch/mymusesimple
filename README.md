# MyMuseSimple for Joomla #

Sell your tunes online. A component for Joomla. This is a test site. Find the official release at http://www.joomlamymuse.com

## The component files are in the src directory ##

* administrator/
* install/
* media/
* modules/
* plugins/
* site/

* manifest.xml - included in the component
* script.php - included in the component
* build.xml - used by phing to copy files into /joomla

### Building ###

* mymake - shell script to aid in building
	$ ./mymake webdriver
	: Will start the chrome webdriver in the background

	$ ./mymake phing
	: Will copy files from src to joomla into correct spots. You need phing installed.

	$ .mymake component
	: Will zip up the component and put copies in /releases and joomla/tests/_data

	$ ./mymake test [sub-test]
	: Will run all acceptance tests, or the one you want (ex. ./mymake test MyMuseBasicCest.php). Do the 'composer' thing below.

### Releases ###

* com_mymusesimple-*****.zip - the latest build
* com_mymusesimple-latest.zip - copy of the latest build

### Testing with Linux ###
Assuming you clone the repository to /var/www/html, you can reach the test install at
http://localhost/mymuse/joomla

To install, rename installation.dist to installation and go to http://localhost/mymuse/joomla

Tests assume the admin account is user: admin, pass: admin.

You can also consult configuration.dist.php.

Mail tests may require using smtpauth if you cannot mail from localhost.

This directory has some files for adding sample data
* joomla/mymuse-downloads

#### Testing with Codeception and Joomla Browser ####
You need 'composer' installed

$ cd joomla

$ composer install

The default is using the chrome webdriver we have put into joomla/bin

Start it in the background like this

	$ ./bin/chromedriver --url-base=/wd/hub &

Run the first test

	$ php vendor/bin/codecept run acceptance AdminLoginCest

If all goes well, try installing MyMuse

	$ php vendor/bin/codecept run acceptance MyMuseBasicCest

OR use the mymake script

	$ ./mymake webdriver

	$ ./mymake component

	$ ./mymake test





