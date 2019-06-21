#!/usr/bin/env bash

set -ex

install_db() {
	mysql -e 'CREATE DATABASE IF NOT EXISTS wp_cli_test;' -uroot -pr00t -h 127.0.0.1
	mysql -e 'GRANT ALL PRIVILEGES ON wp_cli_test.* TO "wp_cli_test"@"127.0.0.1" IDENTIFIED BY "password1"' -uroot -pr00t -h 127.0.0.1
	mysql -e 'GRANT ALL PRIVILEGES ON wp_cli_test_scaffold.* TO "wp_cli_test"@"127.0.0.1" IDENTIFIED BY "password1"' -uroot -pr00t -h 127.0.0.1
}

install_db
