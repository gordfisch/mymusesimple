# MyMuse for Joomla #

Sell your tunes online. Sell your merchandise. A component for Joomla. This is a test site. Find the official release at http://www.joomlamymuse.com

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


### Releases ###

* com_mymusesimple-*****.zip - the latest build
* com_mymusesimple-latest.zip - copy of the latest build

### Testing with Linux ###
If your web root is /var/www/html and you clone the repository there, you can reach the test install at
http://localhost/mymusesimple/joomla

To install, rename installation.dist to installation and go to http://localhost/mymuse/joomla

Tests assume the admin account is user: admin, pass: admin. Database prefix is: bf7gn_
Edit the files joomla/tests/acceptance.suite.yml and reports.suite.yml with database credentials.

You can also consult configuration.dist.php.

Mail tests may require using smtpauth if you cannot mail from localhost.


#### Testing with Codeception and Joomla Browser ####
You need 'composer' installed

$ cd joomla

$ composer install

We use the chrome webdriver we have put into joomla/bin

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

### Building ###

* mymake - shell script to aid in building

	$ ./mymake webdriver
	: Will start the chrome webdriver in the background

	$ ./mymake phing
	: Will copy files from src to joomla into correct spots. You need phing installed: https://www.phing.info/
	
	$ ./mymake phing watch
	: Will copy files from src to joomla into correct spots whenever you save a file.

	$ ./mymake component
	: Will zip up the component and put copies in /releases and joomla/tests/data

	$ ./mymake test [sub-test]
	: Will run all acceptance tests, or the one you want (ex. ./mymake test MyMuseBasicCest.php).


	$ ./mymake all
	: Will make the component then run all tests

	You can also run tests from the command line

	cd joomla

	php vendor/bin/codecept --steps run acceptance

	php vendor/bin/codecept --steps run reports

	php vendor/bin/codecept --steps run storage
	




