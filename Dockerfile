# syntax=docker/dockerfile:1
FROM --platform=linux/amd64 php:8.1.16-fpm-buster

# Docker image context
LABEL Maintainer="Alcides Ramos <info@alcidesramos.com>"
LABEL Description="Lightweight PHP8 development environment"

# Define build arguments
ARG USER_ID
ARG USER_NAME
ARG GROUP_ID
ARG GROUP_NAME

# Add group and user based on build arguments

RUN addgroup --gid ${GROUP_ID} ${GROUP_NAME} \
    && adduser --disabled-password --gecos '' --uid ${USER_ID} --gid ${GROUP_ID} ${USER_NAME}

# Install dependencies via <apt>

RUN apt update && apt upgrade -y && apt install -y --fix-missing \
        zip \
        unzip \
        zlib1g-dev \
        libzip-dev \
    && pecl install pcov \
    && docker-php-ext-install zip \
    && docker-php-ext-enable pcov

# Install cgi-fcgi via <apt-get>

RUN apt-get update && apt-get install -y \
        libfcgi0ldbl

# Install Composer

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Create /output folder with proper permissions

RUN mkdir /output \
    && chown -R ${USER_NAME}:${GROUP_NAME} /output

# Define the working directory

WORKDIR /code
