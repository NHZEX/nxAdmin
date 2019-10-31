FROM php-swoole:latest

MAINTAINER zx

LABEL product=swoole-ops-server

ARG CN="0"

COPY . /opt/ops-server

RUN cd /opt/ops-server \
    && chmod +x ./think ./phinx_cli.php ./docker-php-entrypoint \
    && (rm -r runtime/* .env || true) \
    && ([ "${CN}" = "0" ] || composer config repo.packagist composer https://mirrors.aliyun.com/composer/) \
    && composer install --no-cache --no-progress --no-dev -o \
    && composer clearcache

EXPOSE 9501/tcp 9502/tcp

WORKDIR /opt/ops-server

HEALTHCHECK --interval=10s --timeout=3s --retries=3 \
    CMD php ./think server health -c

ENTRYPOINT ["./docker-php-entrypoint"]
CMD ["php", "./think", "server"]
