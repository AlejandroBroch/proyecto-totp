# Proyecto TOTP - Autenticación en Dos Pasos (2FA)

Este proyecto implementa un sistema de seguridad TOTP utilizando PHP y una base de datos SQLite.

## 🛠️ Tecnologías utilizadas

-PHP 8.x

-SQLite para la persistencia de usuarios.

-Librería robthree/twofactorauth para la lógica TOTP.

-Librería endroid/qr-code para la generación de imágenes QR.


## 🚀 Instalación y Configuración

1. **Clonar el proyecto:**

   ```bash
   git clone <url-de-tu-repo>
   ```
2. **Instalar dependencias**

-> Desde la raíz del proyecto ejecuta: 

 ```bash
 composer install
 ```

3. **Requisitos de PHP**

-> Es necesario tener activa la extensión GD para la generación de códigos QR:

 ```bash
sudo apt install php-gd
 ```

-> Es necesario también instalar por seguridad SQLite:

 ```bash
sudo apt install php-sqlite3
 ```
    
4. **Levantar el servidor**

-> Desde la raíz del proyecto, ejecuta:

 ```bash
 php -S localhost:8000
 ```


### Requisitos de Base de Datos
- PHP debe tener activada la extensión `pdo_sqlite`.
- La carpeta `backend/` debe tener permisos de escritura para que PHP pueda gestionar el archivo de base de datos.
