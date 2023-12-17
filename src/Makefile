start:
	make stop
	docker compose up --build -d
	make logs

stop:
	docker compose down -t 1

logs:
	docker compose logs -f

mysql:
	docker compose exec -it wep-db mysql -uuser -puser123 -Dapp
