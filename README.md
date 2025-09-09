### Setup
```sh
cp -n .env.example .env
docker-compose up -d
```
### Run
```sh
bin/console import:products ./tests/fixture/products.csv
```
