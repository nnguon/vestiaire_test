# vestiaire_test
Assumption:
    php, redis and composer are installed

How to:
1. run composer: composer install
2. run redis server: sudo service redis-server start
3. create .env with following:
    REDIS_HOST='127.0.0.1'
    REDIS_PORT='6379'
    REDIS_PASSWORD=''
    REDIS_DATABASE='0'

4. run server: php -S localhost:8080 index.php
5. use postman to launch query or use curl: 
    authorization : curl -X POST -H "Content-Type: application/json" -d '{"amount": 155, "card_number": "5111111111111111", "expiry_date": "11/26", "cvv": "414"}' http://localhost:8080/authorize
    capture: curl -X POST -H "Content-Type: application/json" -d '{"amount": 155, "auth_token": "trx_67a1853600dc0"}' http://localhost:8080/capture
    refund: curl -X POST -H "Content-Type: application/json" -d '{"amount": 155, "transaction_id": "cap_67a1859c3c2a8"}' http://localhost:8080/refund
Api doc can be found in swagger.yaml

Design pattern used:
    - factory
    - singleton
    - repository/gateway
    - builder

Architecture:
Tried to stay as closed as possible to an clean architecture (Vertical Slice Architecture) and clean code

ApiController handle every api calls (post only)
All domains rules should be in UseCase and every layer should communicate with useCase using interfaces

Limitation:
Api doesnt handle format
Only handle full capture and full refund (no partial)
Integration test not finished# vestiaire_test
# vestiaire-collective-test
# vestiaire-collective
