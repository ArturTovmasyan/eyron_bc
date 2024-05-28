# E2E TESTS RUN
1) Install protractor, codeceptjs and webdriver manager globally

`sudo npm install -g protractor`

`sudo npm install -g codeceptjs`

`sudo npm install -g webdriver-manager`

2) Install all npm packages

`npm install`

3) Create virtual host for backend test environment and add it in /etc/hosts

`<VirtualHost *:80>
     ServerName behat.bucketlist.loc
     DocumentRoot /var/www/html/bucketlist/web/
     DirectoryIndex app_behat.php
     <Directory /var/www/html/bucketlist/web/> 
 	AllowOverride none
 	Require all granted
 	Options Indexes FollowSymLinks MultiViews 
 	<IfModule mod_rewrite.c>
 	     RewriteEngine On
 	     RewriteRule ^app_behat.php - [L]
 	     RewriteRule ^app.php - [L]
 	     RewriteCond %{REQUEST_FILENAME} !-f
 	     RewriteRule ^(.*)$ app_behat.php [QSA,L]
 	</IfModule>
     </Directory>
 </VirtualHost>`

4) Create virtual host for front test environment and add it in /etc/hosts

`<VirtualHost *:80>
     ServerName test.bucketlist.loc
     DocumentRoot /var/www/html/bucketlist/web/test
 	<Directory /var/www/html/bucketlist/web/test> 
 		DirectoryIndex index.html 
 		Options Indexes FollowSymLinks MultiViews 
 		AllowOverride All 
 		Require all granted 
 		RewriteEngine on
             	RewriteCond %{REQUEST_FILENAME} -s [OR]
 	    	RewriteCond %{REQUEST_FILENAME} -l [OR]
 	    	RewriteCond %{REQUEST_FILENAME} -d
 	    	RewriteRule ^.*$ - [NC,L]
 	    	RewriteRule ^(.*) /index.html [NC,L]
 	</Directory>
 </VirtualHost>
 	`

5) Build project with test environment

`./bin/clean.sh`

`./bin/make-desc.sh`

`ng build --env=test --output-pat=../web/test`
	
6) Run web driver manager for testing

`webdriver-manager start`

 if test not run please run bellow commands
 
`sudo webdriver-manager update --versions.chrome 2.26 --versions.standalone 2.53.1`
 
`webdriver-manager start --versions.chrome 2.26 --versions.standalone 2.53.1`
7) Run e2e by sh file in project root directory => bin folder

`./bin/angular-e2e-test.sh`
