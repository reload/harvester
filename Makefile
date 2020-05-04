up:
	docker-compose run server composer install && \
	docker-compose up

create-config:
	cp docker-compose.override.yml-example docker-compose.override.yml

fetch:
	docker-compose exec server app/console harvester:fetch

create-admin:
	docker-compose exec server sh -c 'app/console harvester:user --admin="yes" --active="yes" --password harvest@reload.dk'
