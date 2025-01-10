## CRUD de usuarios con autenticación de Laravel

Este es un proyecto basado en **Laravel 11** utilizando **Laravel Breeze** para la autenticación de usuarios (registro, inicio de sesión, restablecimiento de contraseña)

## Requisitos

Antes de comenzar, asegúrate de tener instalado lo siguiente:

- PHP 8.1 o superior
- Composer
- Laravel 11
- Base de datos MySQL

## Clonando el Repositorio

Puedes clonar el repositorio utilizando el siguiente comando:

```bash
git clone https://github.com/kdam970/gestion-usuarios-laravel.git

# Instala dependencias de composer
composer install

# Instalas dependencias npm
npm install

# Ejecuta las migraciones para crear las tablas necesarias en la base de datos
php artisan migrate

#Finalmente, puedes iniciar el servidor de desarrollo de Laravel
php artisan serve