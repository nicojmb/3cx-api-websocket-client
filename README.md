# 3CX API WebSocket Client

Cliente WebSocket en PHP para interactuar con la API de eventos en tiempo real de 3CX. Este proyecto permite escuchar eventos y enviar comandos al servidor de 3CX utilizando su interfaz WebSocket, ideal para integraciones con sistemas de CRM, dashboards personalizados, o automatización de llamadas.

---

## 📦 Características

- Conexión en tiempo real con 3CX mediante WebSocket
- Envío de solicitudes y recepción de respuestas desde la API
- Manejo de eventos como llamadas entrantes/salientes
- Estructura de código limpia y extensible

---

## 🧰 Requisitos

- PHP 7.4 o superior
- Composer
- Servidor 3CX configurado para aceptar conexiones WebSocket

---

## 🚀 Instalación

1. **Clonar el repositorio**

   ```bash
   git clone https://github.com/nicojmb/3cx-api-websocket-client.git
   cd 3cx-api-websocket-client
   ```

2. **Instalar dependencias**

   ```bash
   composer install
   ```

3. **Configurar el archivo de conexión**

   Modifica el archivo de configuración con tus credenciales y parámetros:

   ```php
   // config/config.php
   return [
       'base_url' => 'https://mycompany.3cx.com',
       'client_id' => 'customphpapp',
       'client_secret' => 'xxxxxxxxxxxxx',
       'keep_alive' => 60
   ];
   ```

---

## 🧪 Ejecución de ejemplo

Puedes correr un ejemplo básico desde el directorio `public/`:

```bash
php public/client.php
```

---

## 📁 Estructura del proyecto

```
.
├── config/             # Configuración de conexión
├── public/             # Ejemplos de uso
├── src/                # Código fuente principal
├── composer.json       # Dependencias del proyecto
└── README.md
```

---

## 🛡️ Seguridad

- No incluyas tus credenciales en el repositorio.
- Asegúrate de usar conexiones WebSocket seguras (wss://).
- Implementa validaciones si usas este cliente en producción.

---

## 🧩 TODO

- [ ] Mejorar manejo de reconexión automática
- [ ] Agregar soporte para múltiples extensiones
- [ ] Agregar pruebas automatizadas
- [ ] Ampliar documentación

---

## 📄 Licencia

Este proyecto se distribuye bajo la licencia MIT. Consulta el archivo `LICENSE` para más información.

---

## 🤝 Contribuciones

¡Contribuciones son bienvenidas! Abre un issue o pull request si quieres mejorar el proyecto o reportar errores.
