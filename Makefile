start:
		docker-compose up -d && docker-compose run app composer update
docs:
		docker-compose run app php artisan l5-swagger:generate && docker-compose run app php artisan l5-swagger:publish
