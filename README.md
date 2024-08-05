# AlphaVantage

Prepare the image
docker compose up --pull always -d --wait

To start the server:
php -S localhost:8000 -t public/

APIS:


http://localhost:8000/api/users (POST) - will create user on our DB.
example: on body , send : {
  "email": "test@test.net"
}

http://localhost:8000/history (GET) - will return a list of requests made by that user
IMPORTANT: basic Auth is needed - send Username as the email registered


http://localhost:8000/stock/%symbol% (GET) - will return the actual data from API stock
IMPORTANT: basic Auth is needed - send Username as the email registered


Don't forget to rename .env.example to .env, and fill the correct values to APP_SECRET, and credencials for MAILER_DSN (for test propouses I recomend mailtrap)
