# Gate Autotests

Автоматические тесты проектов Ротор и Форгейт. 

## Подготовка БД
```
docker exec -it unit php database.php
docker exec -it unit_forgate vendor/bin/phinx migrate -e forgate_green
```

## Запуск тестов
```
docker exec -it unit vendor/bin/phpunit
```
