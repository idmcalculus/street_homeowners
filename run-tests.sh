#!/bin/bash

# Check if Xdebug is installed
if php -m | grep -q xdebug; then
	echo "Xdebug is installed. Running tests with coverage..."
	vendor/bin/phpunit --coverage-html coverage-report
	echo "Coverage report generated in coverage-report directory"
	
	# Open the coverage report if on macOS
	if [[ "$OSTYPE" == "darwin"* ]]; then
		open coverage-report/index.html
	fi
else
	echo "Xdebug is not installed. Running tests without coverage..."
	echo "To enable coverage, install Xdebug and configure php.ini"
	vendor/bin/phpunit
fi