## install

```shell
gh repo clone 021800rr/cs

cd cs

docker compose --file docker/docker-compose.yml --env-file BE/.env build --no-cache --pull
docker compose --file docker/docker-compose.yml --env-file BE/.env up -d

docker exec -it cs-php-dev bash
    cd /var/www/
    composer install
    
    symfony console cache:clear -n --env=dev
    symfony console doctrine:database:drop --force --env=dev || true
    symfony console doctrine:database:create
    symfony console doctrine:migrations:migrate -n --env=dev
    symfony console doctrine:fixtures:load -n --env=dev
    symfony console cache:clear -n --env=dev
    
    mkdir --parents tools/php-cs-fixer
    composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer

    php bin/console lexik:jwt:generate-keypair
    setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
    setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt

docker exec -it cs-postgres-dev bash 
    psql -U postgres -d postgres
        create database cs_dev_test;
        
docker exec -it cs-php-dev bash
    cd /var/www/
    make tests
```

api: http://localhost/api  
  
user: admin@example.com  pass: test  
user: editor@example.com pass: test  
user: user@example.com   pass: test  

### it should be ready now

optional environment shutdown
```    
docker compose --file docker/docker-compose.yml --env-file BE/.env down --remove-orphans
```
