# What is this
This is the [Git repository](https://en.wikipedia.org/wiki/Git) for the code that runs the [Fleming Fund website](http://www.flemingfund.org)

# Tech overview
* Content Management System (CMS): WordPress
* Hosting: AWS
* CI: Travis CI

# Zero-to-hero
* Install Git for Windows (https://git-scm.com/download/win)  
  (optional extra) Install your Git GUI of choice

* Install NPM (part of Node.js) (https://nodejs.org/en/download/)

* Install the latest version of MySQL Community (https://dev.mysql.com/downloads/installer)
  * Note: it asks you to login to an Oracle Web Account  
    Ignore this, just click "No thanks, just start my download."  
    When I ran it, the installer was quite flaky and I had to try it a couple of times.
  * Choose "Custom" install type and choose "Server" and "Workbench"
  * Choose "Use Legacy Authentication Method"  
    (at the time of writing) MySQL Workbench (the GUI) is incompatible with the newer authentication mode (!)
  * Set a root password (for your dev machine, this can be anything, or blank)
  * Use defaults for all other options

* Install the latest version of PHP (https://windows.php.net/download)  
  Use the x64 thread-safe version
  * Extract the zip file somewhere sensible (e.g. C:\Program Files\PHP)
  * Add the folder containing `php.exe` to your PATH environment variable  
    Hint: [Rapid Environment Editor](https://www.rapidee.com/en/download) is a really useful tool for doing this

* Create a `php.ini` file where you just installed PHP.  
  PHP ships with a `php.ini-development` file - copy this to `php.ini`  
  Make the following changes to your `php.ini` file:
  * Un-comment `extension_dir = "ext"`
  * Un-comment `extension=curl`
  * Un-comment `extension=gd2`
  * Un-comment `extension=intl`
  * Un-comment `extension=mbstring`
  * Un-comment `extension=mysqli`
  * Un-comment `extension=openssl`

* Check out this code  
  `git clone git@github.com:Softwire/fleming-fund-website.git`

* Ask someone on the team for the file: `.credentials\aws-credentials-backup-download.json`

* Run `one-click-install.sh`

* Run these two shell scripts to build and serve the website:
  * `dev--build-and-watch.sh`  
  This uses Webpack to compile SCSS etc  
  It watches for changed files and re-compiles automatically.

  * `dev--run-php-server.sh`  
  This runs a simple PHP server in the right folder.  
  **Note:** this file must be run from an existing command prompt  
  e.g. by running `./dev--run-php-server.sh`  
  For some reason, if you just double-click on this shell script in Windows Explorer, it doesn't know where your `php.ini` file is :-(

* If you need to get a fresh copy of the