FROM php:8.3-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql \
    && apk add --no-cache tzdata \
    && cp /usr/share/zoneinfo/Asia/Seoul /etc/localtime \
    && echo "Asia/Seoul" > /etc/timezone

# 세션 저장용 쓰기 가능 디렉토리 (구커널 seccomp 환경 대응)
RUN mkdir -p /var/www/sessions && chmod 1777 /var/www/sessions

RUN { \
      echo "upload_max_filesize=20M"; \
      echo "post_max_size=25M"; \
      echo "memory_limit=256M"; \
      echo "date.timezone=Asia/Seoul"; \
      echo "expose_php=Off"; \
      echo "session.save_path=/var/www/sessions"; \
    } > /usr/local/etc/php/conf.d/zz-arno.ini

WORKDIR /var/www/html
