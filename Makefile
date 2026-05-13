.PHONY: validate install update php/deps php/check php/fix php/min-compatibility php/max-compatibility php/phpstan php/analyze php/tests php/test php/testdox ci clean

PHP_MIN_VERSION := "8.1"
PHP_MAX_VERSION := "8.5"
COMPOSER_BIN := composer
define header =
    @if [ -t 1 ]; then printf "\n\e[37m\e[100m  \e[104m $(1) \e[0m\n"; else printf "\n### $(1)\n"; fi
endef

#~ Composer dependency
validate:
	$(call header,Composer Validation)
	@${COMPOSER_BIN} validate

install:
	$(call header,Composer Install)
	@${COMPOSER_BIN} install

update:
	$(call header,Composer Update)
	@${COMPOSER_BIN} update
	@${COMPOSER_BIN} bump --dev-only

composer.lock: install

#~ Vendor binaries dependencies
vendor/bin/php-cs-fixer: composer.lock
vendor/bin/phpstan: composer.lock
vendor/bin/phpunit: composer.lock

#~ Report directories dependencies
build/reports/phpunit:
	@mkdir -p build/reports/phpunit

build/reports/phpstan:
	@mkdir -p build/reports/phpstan

#~ main commands
php/deps: composer.json
	$(call header,Checking Dependencies)
	@XDEBUG_MODE=off ./vendor/bin/composer-dependency-analyser --config ./ci/composer-dependency-analyser.php # for shadow, unused required dependencies and ext-* missing dependencies

php/check: vendor/bin/php-cs-fixer
	$(call header,Checking Code Style)
	@XDEBUG_MODE=off ./vendor/bin/php-cs-fixer check
php/fix: vendor/bin/php-cs-fixer
	$(call header,Fixing Code Style)
	@XDEBUG_MODE=off ./vendor/bin/php-cs-fixer fix -v

php/min-compatibility: vendor/bin/phpstan build/reports/phpstan
	$(call header,Checking PHP ${PHP_MIN_VERSION} compatibility)
	@XDEBUG_MODE=off ./vendor/bin/phpstan analyse --configuration=./ci/phpmin-compatibility.neon --error-format=table

php/max-compatibility: vendor/bin/phpstan build/reports/phpstan #ci
	$(call header,Checking PHP ${PHP_MAX_VERSION} compatibility)
	@XDEBUG_MODE=off ./vendor/bin/phpstan analyse --configuration=./ci/phpmax-compatibility.neon --error-format=table

php/analyze: vendor/bin/phpstan build/reports/phpstan #manual & ci
	$(call header,Running Static Analyze - Pretty tty format)
	@XDEBUG_MODE=off ./vendor/bin/phpstan analyse --error-format=table

php/tests: vendor/bin/phpunit build/reports/phpunit #ci
	$(call header,Running Unit Tests)
	@XDEBUG_MODE=coverage php ./vendor/bin/phpunit --testsuite=unit --coverage-clover=./build/reports/phpunit/clover.xml --log-junit=./build/reports/phpunit/unit.xml --coverage-php=./build/reports/phpunit/unit.cov --coverage-html=./build/reports/coverage/ --fail-on-warning

php/test: php/tests

php/integration: vendor/bin/phpunit build/reports/phpunit #manual
	$(call header,Running Integration Tests)
	@XDEBUG_MODE=coverage php ./vendor/bin/phpunit --testsuite=integration --fail-on-warning

php/testdox: vendor/bin/phpunit #manual
	$(call header,Running Unit Tests (Pretty format))
	@XDEBUG_MODE=coverage php ./vendor/bin/phpunit --testsuite=unit --fail-on-warning --testdox

clean:
	$(call header,Cleaning previous build) #manual
	@if [ "$(shell ls -A ./build)" ]; then rm -rf ./build/*; fi; echo " done"

ci: clean validate install php/deps php/check php/tests php/integration php/min-compatibility php/max-compatibility php/analyze
