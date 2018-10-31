include .env

.PHONY: up down stop prune ps shell

default: up

up:
	@echo "Starting up containers for $(PROJECT_NAME)..."
	docker-compose pull --parallel
	docker-sync start -n docker-sync-$(PROJECT_NAME)
	docker-compose up -d
	docker-compose -f traefik.yml up -d

down: stop

stop:
	@echo "Stopping containers for $(PROJECT_NAME)..."
	docker-compose stop
	docker-compose -f traefik.yml stop
	docker-sync stop

prune:
	@echo "Removing containers for $(PROJECT_NAME)..."
	docker-compose down -v
	docker-compose -f traefik.yml down

ps:
	@docker ps --filter name='$(PROJECT_NAME)*'

shell:
	docker exec -ti $(shell docker ps --filter name='$(PROJECT_NAME)_php' --format "{{ .ID }}") sh

setup_default_core:
	docker exec -ti $(shell docker ps --filter name='$(PROJECT_NAME)_solr' --format "{{ .ID }}") make init -f /usr/local/bin/actions.mk

install_dev_modules:
	docker exec -ti $(shell docker ps --filter name='$(PROJECT_NAME)_php' --format "{{ .ID }}") sh -c 'cd www && drush en $(DEV_MODULES) -y'

update_and_revert:
	docker exec -ti $(shell docker ps --filter name='$(PROJECT_NAME)_php' --format "{{ .ID }}") sh -c 'cd www && drush updb -y && drush cim -y --partial --source=/var/www/html/config/sync && drush cr all && exit'

set_permissions:
	docker exec -ti $(shell docker ps --filter name='$(PROJECT_NAME)_php' --format "{{ .ID }}") sh -c 'sudo addgroup wodby www-data && sudo mkdir -p ./vendor/composer/installers && sudo find ./vendor -type d -exec sudo chmod ug=rwx,o=r '{}' \; && sudo find ./vendor -type f -exec sudo chmod ug=rw,o=r '{}' \; && sudo chown -R www-data:www-data ./vendor && sudo chmod +x fix-drupal-permissions.sh && sudo ./fix-drupal-permissions.sh --drupal_path=./www --drupal_user=www-data --httpd_group=www-data'

composer_update:
	docker exec -ti $(shell docker ps --filter name='$(PROJECT_NAME)_php' --format "{{ .ID }}") sh -c 'composer install && composer update'

first_setup:
	make setup_default_core
	make update_and_revert
	make set_permissions
	make install_dev_modules
	make composer_update
