# TP Final Desafío - Juego de Preguntas y Respuestas

**Programación Web 2 - UNLaM - 2025 2do Cuatrimestre**

---

## 📋 Descripción del Proyecto

📋 Descripción del Proyecto
Plataforma web interactiva de preguntas y respuestas estilo trivia, diseñada para ofrecer una experiencia de juego educativa y entretenida. El proyecto integra funcionalidades de gamificación, estadísticas en tiempo real y un sistema de perfiles completo.
🎯 Objetivos

Crear una plataforma escalable y responsive (mobile-first)
Implementar un sistema de usuarios con roles diferenciados
Desarrollar mecánicas de juego adaptativas según el nivel del jugador
Generar estadísticas detalladas para análisis de uso
Permitir monetización y personalización para entidades externas

🌟 Características Principales

Sistema de autenticación robusto con verificación por email
Ranking global y perfiles públicos con QR
Categorización de preguntas por temas con identificación visual
Sistema de reportes para control de calidad de contenido
Dashboard administrativo con gráficos y métricas
Modo multijugador con desafíos entre usuarios
Monetización mediante sistema de "trampitas"
White-label para empresas y organizaciones educativas
---

## 👤 Sistema de Usuarios

### Registro de Usuario
El registro inicial solicita los siguientes datos:
- Nombre completo
- Año de nacimiento
- Sexo (Masculino, Femenino, Prefiero no cargarlo)
- País y ciudad (selección mediante mapa)
- Email
- Contraseña (con confirmación)
- Nombre de usuario
- Foto de perfil

**Validación:** Se enviará un email con un link de validación para habilitar la cuenta.

### Tipos de Usuario

#### 🎮 Usuario Jugador
- Acceso al lobby con nombre y puntaje en ranking
- Crear nuevas partidas
- Reportar preguntas inválidas
- Crear preguntas nuevas
- Ver perfil propio y de otros jugadores

#### ✏️ Usuario Editor
- Alta, baja y modificación de preguntas
- Revisar preguntas reportadas (aprobar/rechazar)
- Aprobar preguntas sugeridas por usuarios

#### 👨‍💼 Usuario Administrador
Acceso a estadísticas y reportes:
- Cantidad de jugadores totales
- Cantidad de partidas jugadas
- Cantidad de preguntas en el juego
- Cantidad de preguntas creadas
- Cantidad de usuarios nuevos
- Porcentaje de respuestas correctas por usuario
- Usuarios por país
- Usuarios por sexo
- Usuarios por grupo de edad (menores, medios, jubilados)

**Filtros disponibles:** Día, semana, mes, año  
**Funcionalidad:** Impresión de reportes (tablas de datos)

---

## 🎯 Lobby Principal

El lobby incluye:
- Título con nombre de usuario y puntaje en ranking
- Botón para crear nuevas partidas
- Acceso al ranking global
- Listado de partidas jugadas con resultados

### Perfil de Jugador
Al hacer clic en cualquier jugador se puede ver:
- Datos personales (con mapa de ubicación)
- Nombre y puntaje final
- Partidas realizadas
- Código QR para acceso rápido al perfil

---

## 🎲 Mecánica del Juego

### Sistema de Preguntas
- Formato: Opción múltiple (ABCD)
- Selección aleatoria de preguntas
- Cada respuesta correcta suma 1 punto
- Al fallar, termina la partida
- Se muestra el puntaje final o la respuesta correcta

### Categorías
Las preguntas se organizan por categorías:
- Historia
- Deportes
- Cultura
- *(y otras a definir)*

Cada categoría tiene un color distintivo que se muestra en pantalla.

---

## 🚀 Features Adicionales

### 🎓 Facilidad de Uso
- **Preguntas no repetidas:** Los usuarios no verán preguntas ya respondidas hasta agotar el banco de preguntas
- **Dificultad adaptativa:**
    - Pregunta fácil: >70% de respuestas correctas
    - Pregunta difícil: <30% de respuestas correctas
    - El sistema ajusta la dificultad según el ratio de aciertos del usuario

### 💰 Monetización: Trampitas
- **Producto:** "Trampita" - permite responder correctamente sin conocer la respuesta
- **Precio:** $1 USD por trampita
- **Visualización:** El usuario ve su cantidad de trampitas disponibles
- **Compra:** Sistema de compra integrado (simulación de pago, posible integración con MercadoPago)
- **Reportes admin:**
    - Balance de trampitas por usuario
    - Ingresos generados por trampitas

### 👥 Modo Social
- **Partidas entre jugadores:**
    - Desafiar a otro jugador desde su perfil
    - Gana quien responda más preguntas correctamente
    - Identificación especial en lista de partidas
    - Los desafíos pendientes aparecen en el lobby del retado
- **Modo bot:** Inicialmente el oponente será un bot

### 🏢 Venta a Terceros
Sistema de personalización para empresas y colegios:
- **Uso:** Exámenes interactivos, entretenimiento en salas de espera
- **Funcionalidad:**
    - Escaneo de código QR del establecimiento
    - Durante 1 hora: preguntas específicas del establecimiento
    - Ranking segmentado por establecimiento (no global)
    - Aplicable a hospitales, bancos, colegios, etc.

### ✨ Mejora de UX
- Animaciones y transiciones
- Música de fondo (con opción de silenciar)

---

## 🛠️ Condiciones Técnicas

### Tecnologías
- **Backend:** PHP puro (sin frameworks)
- **Base de datos:** MySQL o PostgreSQL
- **Arquitectura:** Modelo MVC (proporcionado en clase)

### Validaciones
- Toda la lógica y validaciones deben estar del lado del servidor
- Las validaciones del cliente son solo para mejorar UX y animaciones

### Restricciones
- ❌ No se permite uso de frameworks
- ✅ Consultar por librerías de terceros antes de implementar
- ✅ Uso del modelo MVC de ejemplo dado en clase

---

## 👥 Modalidad de Trabajo

### Equipo
- Trabajo grupal de **4 integrantes**

### Entregas
- Avances semanales
- Exposición final del trabajo completo
- Defensa oral
