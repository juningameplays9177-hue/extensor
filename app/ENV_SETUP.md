# Configuração do .env

## Importante: APP_KEY

O arquivo `.env` DEVE conter a linha `APP_KEY=` (mesmo que vazia) para que o Laravel possa gerar a chave.

## Configuração completa do .env para produção

```env
APP_NAME="Top Rio"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://darkgoldenrod-tarsier-615186.hostingersite.com

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u494944867_toprio
DB_USERNAME=u494944867_admintoprio
DB_PASSWORD=Blade1411@20

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync

CACHE_STORE=file

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="noreply@toprio.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

## Após fazer upload

1. Certifique-se de que a linha `APP_KEY=` existe no `.env`
2. Execute: `php artisan key:generate --force`
3. Execute: `php artisan config:cache`
