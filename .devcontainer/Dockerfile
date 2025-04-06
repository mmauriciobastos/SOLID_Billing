FROM mcr.microsoft.com/devcontainers/php:8.3-bookworm

# Install dependencies for the PHP extensions
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y libzip-dev unzip postgresql-client libpq-dev libicu-dev libxslt-dev libpng-dev librabbitmq-dev libsodium-dev \ 
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*
    
# Install PHP extensions
RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install amqp && docker-php-ext-enable amqp
RUN docker-php-ext-install pdo_pgsql intl sodium

# [Optional] Uncomment this section to install additional OS packages.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here>

# [Optional] Uncomment this line to install global node packages.
# RUN su vscode -c "source /usr/local/share/nvm/nvm.sh && npm install -g <your-package-here>" 2>&1

