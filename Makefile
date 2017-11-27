start:
		docker-compose up -d && docker-compose run app composer update
docs:
		docker-compose run --rm app php artisan l5-swagger:generate && docker-compose run --rm app php artisan l5-swagger:publish
test:
		docker-compose run --rm app ./vendor/bin/phpunit
