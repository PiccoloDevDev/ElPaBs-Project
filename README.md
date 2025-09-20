ElPaBs

Una aplicación web que permite realizar cobros en tiempo real durante sesiones tipo Kahoot, utilizando la API de Interledger Open Payments para dividir el pago entre múltiples participantes de forma segura y eficiente.

Características

· Pagos en Tiempo Real: Integración con Interledger para procesamiento instantáneo de pagos
· División Automática: Distribución equitativa del costo total entre todos los participantes
· Sala de Quiz Interactiva: Interfaz similar a Kahoot para experiencias engaging
· Dashboard para Organizadores: Panel para crear sesiones y monitorear pagos
· Notificaciones Instantáneas: Confirmación de pagos exitosos o fallidos

Prerrequisitos

Antes de ejecutar esta aplicación, asegúrate de tener instalado:

· Node.js (v16 o superior)
· npm o yarn
· Una cuenta en un proveedor de billetera Interledger (Rafiki, Xpring, etc.)
· Credenciales de la API de Open Payments

Uso

Para Organizadores:

1. Inicia sesión en tu cuenta de QuizPay
2. Crea una nueva sala de quiz y establece el precio total
3. Comparte el código de la sala con los participantes
4. Inicia la sesión y monitorea los pagos en tiempo real

Para Participantes:

1. Ingresa a la aplicación con el código proporcionado
2. Conecta tu billetera Interledger
3. Autoriza el pago de tu parte proporcional
4. ¡Disfruta del quiz!

Configuración de la API de Open Payments

Esta aplicación utiliza la API de Interledger Open Payments para procesar transacciones. Debes:

1. Registrarte en un proveedor compatible con Open Payments
2. Obtener tus credenciales API (client ID y secret)
3. Configurar tu webhook endpoint para recibir notificaciones de pago
4. Configurar las direcciones de tu billetera ILP

Consulta la documentación oficial de Open Payments para más detalles.

🤝 Contribución

Las contribuciones son siempre bienvenidas. Para contribuir:

1. Haz fork del proyecto
2. Crea una rama para tu feature (git checkout -b feature/AmazingFeature)
3. Commit tus cambios (git commit -m 'Add some AmazingFeature')
4. Push a la rama (git push origin feature/AmazingFeature)
5. Abre un Pull Request

Por favor, asegúrate de actualizar los tests según corresponda.

Licencia

Este proyecto está bajo la Licencia MIT. Consulta el archivo LICENSE para más detalles.

Soporte

Si encuentras algún problema o tienes preguntas:

1. Revisa la documentación de Open Payments
2. Busca en los issues existentes
3. Abre un nuevo issue describiendo tu problema

Próximas Actualizaciones

· Integración con más proveedores de wallets
· Soporte para múltiples divisas y conversión
· Modo de práctica sin transacciones reales
· Analytics avanzado de sesiones
· API pública para desarrolladores

Disclaimer: Esta es una aplicación de demostración. Asegúrate de cumplir con todas las regulaciones financieras locales antes de implementar un sistema de pagos real.