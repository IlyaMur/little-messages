install:
	composer install
lint:
	composer exec phpcs -- --standard=PSR12 src
test:
	composer exec --verbose phpunit -- --testsuite gh-actions