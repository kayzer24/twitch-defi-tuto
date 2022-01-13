.PHONY: test install fixtures database prepare tests phpstan php-cs-fixer composer-valid doctrine  fix analyse

install:
	cp .env.dist .env.$(env).local
	sed -i -e 's/DATABASE_USER/$(db_user)' .env.$(env).local
	sed -i -e 's/DATABASE_PASSWORD/$(db_password)' .env.$(env).local
	sed -i -e 's/ENV/$(env)' .env.$(env).local
	compooser install
	make prepare env=$(env)
	npm install
	npm run dev

fixtures:
	symfony console d:f:l -n --env=$(env)

database:
	symfony console doctrine:database:drop --if-exists --force --env=$(env)
	symfony console d:d:c --env=$(env)
	symfony console doctrine:query:sql "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));" --env=$(env)
	symfony console doctrine:schema:update --force --env=$(env)

prepare:
	make database env=$(env)
	make fixtures env=$(env)

tests:
	symfony php vendor/bin/phpunit

eslint:
	npx eslint assets/

stylelint:
	npx stylelint "assets/styles/**/*.scss"

phpstan:
	symfony php vendor/bin/phpstan analyse -c phpstan.neon

php-cs-fixer:
	symfony php vendor/bin/php-cs-fixer fix

composer-valid:
	composer valid

doctrine:
	symfony console doctrine:schema:valid --skip-sync

twig:
	symfony console lint:twig templates

yaml:
	symfony console lint:yaml config translations

container:
	symfony console lint:container

fix: php-cs-fixer
	npx eslint assets/ --fix
	npx stylelint "assets/styles/**/*.scss" --fix

analyse: eslint stylelint twig yaml composer-valid container doctrine phpstan
