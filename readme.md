https://stackoverflow.com/questions/39100023/lexikjwtauthenticationbundle-generate-token
user creation for jwt

@todo:: controller validations
@todo:: unique index in login
@todo:: add *.pem into repository?

# Install
* git clone https://github.com/am0nshi/symfony-4-todo-experiement.git
* cd symfony-4-todo-experiement && composer install
* .env -> DATABASE_URL=mysql://dbuser:dbpw@mysql:3306/docker_symfony4

# Run
* docker-compose up
* docker-compose run php-fpm bin/console doctrine:migrations:migrate 

# Token 
POST http://localhost:8000/api/login_check {_username: johndoe, _password: test}

# Run tests
* cp phpunit.xml.dist phpunit.xml
* setup `DATABASE_URL` in phpunit.xml
* ./bin/phpunit

# FYI
.env and ./config/jwt/*.pem are added into repo to avoid it's personal generation. Its a security issue, but it's not a real project :)