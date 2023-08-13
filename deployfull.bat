call bootstrap.bat
call css.bat
call js.bat
call composer install --no-dev
call composer dump-autoload -o
call php bin/pre-deploy.php
call php ../Deployment/deployment.php ./deployment.ini
call php bin/post-deploy.php
