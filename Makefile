.PHONY: validate install update phpcs phpcbf php74compatibility php81compatibility phpstan analyze tests testdox ci clean

PHP_FILES := $(shell find src tests -type f -name '*.php')
define header =
    @if [ -t 1 ]; then printf "\n\e[37m\e[100m  \e[104m $(1) \e[0m\n"; else printf "\n### $(1)\n"; fi
endef

#~ Composer dependency
validate:
	$(call header,Composer Validation)
	@composer validate

install:
	$(call header,Composer Install)
	@composer install

update:
	$(call header,Composer Update)
	@composer update

composer.lock: install

#~ Vendor binaries dependencies
vendor/bin/phpcbf: composer.lock
vendor/bin/phpcs: composer.lock
vendor/bin/phpstan: composer.lock
vendor/bin/phpunit: composer.lock

#~ Report directories dependencies
build/reports/phpunit:
	@mkdir -p build/reports/phpunit

build/reports/phpcs:
	@mkdir -p build/reports/cs

build/reports/phpstan:
	@mkdir -p build/reports/phpstan

#~ main commands
phpcs: vendor/bin/phpcs build/reports/phpcs
	$(call header,Checking Code Style)
	@./vendor/bin/phpcs --standard=./ci/phpcs/eureka.xml --cache=./build/cs_eureka.cache -p --report-full --report-checkstyle=./build/reports/cs/eureka.xml src/ tests/

phpcbf: vendor/bin/phpcbf
	$(call header,Fixing Code Style)
	@./vendor/bin/phpcbf --standard=./ci/phpcs/eureka.xml src/ tests/

php74compatibility: vendor/bin/phpstan build/reports/phpstan
	$(call header,Checking PHP 7.4 compatibility)
	@./vendor/bin/phpstan analyse --configuration=./ci/php74-compatibility.neon --error-format=table

php81compatibility: vendor/bin/phpstan build/reports/phpstan
	$(call header,Checking PHP 8.1 compatibility)
	@./vendor/bin/phpstan analyse --configuration=./ci/php81-compatibility.neon --error-format=table

analyze: vendor/bin/phpstan build/reports/phpstan
	$(call header,Running Static Analyze - Pretty tty format)
	@./vendor/bin/phpstan analyse --error-format=table

phpstan: vendor/bin/phpstan build/reports/phpstan
	$(call header,Running Static Analyze)
	@./vendor/bin/phpstan analyse --error-format=checkstyle > ./build/reports/phpstan/phpstan.xml

tests: vendor/bin/phpunit build/reports/phpunit $(PHP_FILES)
	$(call header,Running Unit Tests)
	@XDEBUG_MODE=coverage php -dzend_extension=xdebug.so ./vendor/bin/phpunit --coverage-clover=./build/reports/phpunit/clover.xml --log-junit=./build/reports/phpunit/unit.xml --coverage-php=./build/reports/phpunit/unit.cov --coverage-html=./build/reports/coverage/ --fail-on-warning

testdox: vendor/bin/phpunit $(PHP_FILES)
	$(call header,Running Unit Tests (Pretty format))
	@XDEBUG_MODE=coverage php -dzend_extension=xdebug.so ./vendor/bin/phpunit --fail-on-warning --testdox

clean:
	$(call header,Cleaning previous build)
	@if [ "$(shell ls -A ./build)" ]; then rm -rf ./build/*; fi; echo " done"

ci: clean validate install phpcs tests php74compatibility php81compatibility analyze
