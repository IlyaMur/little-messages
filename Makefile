install:
	composer install
	
test:
	composer exec phpunit tests

test-coverage:
	composer exec --verbose phpunit -- --testsuite gh-actions --coverage-clover build/logs/clover.xml
	
lint:
	composer exec phpcs -- --standard=PSR12 src