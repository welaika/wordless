#!/bin/bash
cd ../..
php ./extensions/coverage/bin/php-coverage-open.php \
	--exclude='.*/test/.*' \
    --exclude='.*DB.common.php$' \
    --exclude='.*sqlite.php$' \
    --exclude='.*unit_tests.php$'\
    --exclude='.*PEAR.php$'

# run all tests
php -d auto_prepend_file=./extensions/coverage/autocoverage.php -f test/unit_tests.php    
php -d auto_prepend_file=./extensions/coverage/autocoverage.php -f extensions/coverage/test/test.php    

php ./extensions/coverage/bin/php-coverage-close.php
php ./extensions/coverage/bin/php-coverage-report.php
