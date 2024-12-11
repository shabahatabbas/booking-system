# Dockerfile
FROM php:7.4-apache

# Install the calendar extension
RUN docker-php-ext-install calendar