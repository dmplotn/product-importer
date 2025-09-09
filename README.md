### Setup
```sh
cp -n .env.example .env
docker-compose up -d
bin/console doctrine:migrations:migrate
```
### Run
```sh
bin/console import:products ./tests/fixture/products.csv
```
