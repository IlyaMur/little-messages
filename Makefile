install:
	composer install

lint:
	composer exec phpcs -- --standard=PSR12 src

test:
	composer exec --verbose phpunit -- --testsuite gh-actions

docker-start: 
	docker-compose up -d
	make docker-install

docker-stop: 
	docker-compose down 

docker-install:
	docker exec -it application composer install

docker-test:
	docker exec -it application make test

docker-bash:
	docker exec -it application bash

env-prepare:
	cp -n .env.example .env || true
