FROM composer AS build

WORKDIR /app
COPY composer.* /app/
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-progress

FROM php:8.1

COPY --from=build /app/ /app/
COPY checker /app/checker
COPY src/ /app/src/

ARG lpsc_repo=https://github.com/fabpot/local-php-security-checker
ARG lpsc_path=/usr/local/bin/lpsc
ADD ${lpsc_repo}/releases/download/v2.0.3/local-php-security-checker_2.0.3_linux_amd64 ${lpsc_path}
RUN chmod 755 ${lpsc_path}

ENTRYPOINT ["/app/checker", "check"]
CMD [""]
