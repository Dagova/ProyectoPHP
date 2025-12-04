# BlogPersonal

> Una plataforma moderna tipo blog con sistema de comentarios, control de roles y moderación de contenido

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## Descripción General

**BlogPersonal** es una aplicación web de gestión de blogs desarrollada con PHP vanilla siguiendo el patrón de arquitectura MVC (Modelo-Vista-Controlador). Permite a los usuarios crear y gestionar publicaciones personales, interactuar mediante comentarios y ofrece herramientas administrativas para la moderación de contenido.

### Características Principales

- **Sistema de Autenticación**: Registro, login y gestión de sesiones
- **Gestión de Posts**: Crear, visualizar y eliminar publicaciones
- **Sistema de Comentarios**: Interacción entre usuarios con comentarios anidados
- **Roles de Usuario**: Diferenciación entre usuarios normales y administradores
- **Censura de Contenido**: Sistema dinámico de filtrado de palabras prohibidas
- **Temas Visuales**: Dos temas intercambiables (Neón y Empresarial)
- **Notificaciones Toast**: Feedback visual para acciones del usuario

---S

##  Estructura del Proyecto

```
BlogPersonal/
│
├── BBDD/                      # Capa de Base de Datos
│   ├── Conexion.php          # Singleton para conexión PDO
│   └── setup_db.php          # Script de inicialización de tablas
│
├── Controllers/               # Controladores MVC
│
├── UserController.php    # Gestión de autenticación y usuarios
│   ├── PostController.php    # CRUD de publicaciones
│   └── ComentarioController.php  # Gestión de comentarios y censura
│
├── Models/                    # Modelos de Dominio
│   ├── User.php              # Entidad Usuario
│   ├── Post.php              # Entidad Post
│   ├── Comentario.php        # Entidad Comentario
│   └── Censura.php           # Lógica de filtrado de contenido
│
├── PDO/                       # Capa de Acceso a Datos
│   ├── UserPDO.php           # Repository para usuarios
│   ├── PostPDO.php           # Repository para posts
│   ├── ComentarioPDO.php     # Repository para comentarios
│   └── CensuraPDO.php        # Repository para palabras prohibidas
│
└── views/                     # Capa de Presentación
│   ├── HTML/                 # Templates HTML
│   │   ├── Login.html
│   │   ├── Registro.html
│    │   └── Index.html        # Interfaz principal del blog
│   ├── CSS/                  # Estilos
│   │   ├── Login.css
│   │   ├── Index-neon.css
│   │   └── Index-business.css
│   └── JavaScript/           # Lógica del cliente
│       ├── index.js          # Gestión del blog principal
│       └── auth.js           # Notificaciones de autenticación
│
└── blogpersonal.sql          # Tablas de base de datos

```

---

## Descripción de Clases

### Capa de Modelos

#### `User`
**Responsabilidad**: Entidad de dominio que representa un usuario del sistema.

**Atributos**:
- `id_user`: Identificador único
- `nombre`: Nombre de usuario
- `password`: Contraseña hasheada
- `fecha_registro`: Timestamp de registro
- `rol`: Rol del usuario (`'user'` o `'admin'`)

**Métodos principales**:
- `isAdmin()`: Verifica si el usuario tiene privilegios de administrador

---

#### `Post`
**Responsabilidad**: Representa una publicación del blog.

**Atributos**:
- `id_post`: Identificador único
- `titulo`: Título de la publicación
- `contenido`: Cuerpo del post
- `fecha_creacion`: Timestamp de creación
- `id_user`: ID del autor

---

#### `Comentario`
**Responsabilidad**: Representa un comentario asociado a un post.

**Atributos**:
- `id_comentario`: Identificador único
- `id_post`: ID del post al que pertenece
- `id_user`: ID del autor del comentario
- `contenido`: Texto del comentario
- `fecha_creacion`: Timestamp
- `nombre_usuario`: Nombre del autor (join con users)

---

#### `Censura`
**Responsabilidad**: Gestiona el filtrado de palabras prohibidas en contenido.

**Funcionalidades**:
- Detección de palabras prohibidas mediante expresiones regulares
- Censura automática reemplazando con asteriscos
- Gestión dinámica de la lista de palabras
- Case-insensitive matching

**Métodos principales**:
```php
detectarPalabrasProhibidas(string $texto): array  // Retorna palabras encontradas
censurarTexto(string $texto): string              // Retorna texto censurado
esSeguro(string $texto): bool                     // Valida si el texto es apropiado
```

---

### Capa de Acceso a Datos (PDO)

#### `UserPDO`
**Responsabilidad**: Persistencia de usuarios con validación de duplicados.

**Métodos**:
- `insertUser(User $user)`: Registra nuevo usuario (verifica unicidad)
- `fetchUserById(int $id)`: Obtiene usuario por ID
- `fetchUserByName(string $nombre)`: Busca usuario para login

---

#### `PostPDO`
**Responsabilidad**: Repository para operaciones CRUD de posts.

**Métodos**:
- `insertPost(Post $post)`: Crea nueva publicación
- `fetchAllPosts()`: Lista todos los posts (con autor)
- `mis_posts(string $nombre)`: Filtra posts por usuario
- `deletePost(int $id_post)`: Elimina publicación
- `isOwner(int $id_post, int $id_user)`: Verifica propiedad

---

#### `ComentarioPDO`
**Responsabilidad**: Gestión de comentarios con validación de ownership.

**Métodos**:
- `insertComentario(Comentario $comentario)`: Crea comentario
- `obtenerComentariosPorPost(int $id_post)`: Lista comentarios de un post
- `deleteComentario(int $id_comentario)`: Elimina comentario
- `isOwner(int $id_comentario, int $id_user)`: Verifica propiedad

---

#### `CensuraPDO`
**Responsabilidad**: Persistencia de palabras prohibidas.

**Métodos**:
- `obtenerPalabras()`: Retorna array de palabras prohibidas
- `agregarPalabra(string $palabra)`: Añade nueva palabra
- `eliminarPalabra(string $palabra)`: Remueve palabra

---

### Capa de Controladores

#### `UserController`
**Responsabilidad**: Manejo de autenticación y sesiones.

**Funcionalidades**:
- Registro con hashing de contraseñas (`password_hash`)
- Login con verificación segura (`password_verify`)
- Gestión de sesiones PHP
- Redirección con parámetros de estado

**Endpoints**:
```
POST ?action=login       → Autentica usuario
POST ?action=registrar   → Crea nuevo usuario
GET  ?action=perfil      → Retorna datos del usuario logueado (JSON)
GET  ?action=logout      → Destruye sesión
```

---

#### `PostController`
**Responsabilidad**: CRUD de publicaciones con censura automática.

**Funcionalidades**:
- Creación de posts con censura en título y contenido
- Filtrado por usuario
- Eliminación con validación de permisos (owner o admin)

**Endpoints**:
```
POST ?action=crear_post    → Crea post censurado
GET  ?action=mis_posts     → Posts del usuario actual
GET  ?action=todos_posts   → Todos los posts (solo admin)
POST ?action=buscar_post   → Posts de un usuario específico
POST ?action=eliminar_post → Elimina post (owner o admin)
```

---

#### `ComentarioController`
**Responsabilidad**: Gestión de comentarios y administración de censura.

**Funcionalidades**:
- CRUD de comentarios con censura automática
- Gestión de palabras prohibidas (solo admin)
- Validación de permisos para eliminación

**Endpoints**:
```
POST ?action=crear_comentario      → Crea comentario censurado
POST ?action=obtener_comentarios   → Lista comentarios de un post
POST ?action=eliminar_comentario   → Elimina comentario (owner o admin)
GET  ?action=obtener_palabras      → Lista palabras prohibidas (admin)
POST ?action=agregar_palabra       → Añade palabra (admin)
POST ?action=remover_palabra       → Elimina palabra (admin)
```

---

### Capa de Base de Datos

#### `Conexion`
**Responsabilidad**: Singleton para gestión de conexión PDO.

**Configuración**:
```php
DB_HOST: "localhost"
DB_NAME: "blogpersonal"
DB_USER: "root"
DB_PASS: ""
DB_CHARSET: "utf8mb4"
```

**Características**:
- PDO con prepared statements (seguridad contra SQL injection)
- Manejo de errores con excepciones
- Fetch mode asociativo por defecto

---

## Esquema de Base de Datos

### Tabla: `users`
```sql
id_user         INT PRIMARY KEY AUTO_INCREMENT
nombre          VARCHAR(255) UNIQUE NOT NULL
password        VARCHAR(255) NOT NULL        -- Hash bcrypt
Fecha_Registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
rol             ENUM('user', 'admin') DEFAULT 'user'
```

### Tabla: `posts`
```sql
id_post         INT PRIMARY KEY AUTO_INCREMENT
titulo          VARCHAR(255) NOT NULL
contenido       TEXT NOT NULL
fecha_creacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
id_user         INT NOT NULL
FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
```

### Tabla: `comentarios`
```sql
id_comentario   INT PRIMARY KEY AUTO_INCREMENT
id_post         INT NOT NULL
id_user         INT NOT NULL
contenido       TEXT NOT NULL
fecha_creacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
FOREIGN KEY (id_post) REFERENCES posts(id_post) ON DELETE CASCADE
FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
```

### Tabla: `palabras_prohibidas`
```sql
id              INT PRIMARY KEY AUTO_INCREMENT
palabra         VARCHAR(255) UNIQUE NOT NULL
fecha_creacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

---

## Instalación y Uso

### Requisitos Previos

- **PHP** >= 8.0
- **MySQL** >= 8.0
- **Servidor Local** (XAMPP)
- **Extensiones PHP**: PDO, pdo_mysql

### Pasos de Instalación

#### 1. Clonar el Repositorio
```bash
git clone https://github.com/Dagova/David.G.V-DAM/tree/5e740fceaf298cdbdea93b3bdd39b77b944dc08e/SegundoA%C3%B1o/Trimestre%201/BlogPersonal
cd BlogPersonal
```

#### 2. Configurar Base de Datos

- inicia XAMPP con mySQL
- Dentro de mySQL inicia la BBDD de blogpersonal.sql dentro del trabajo en la carpeta BBDD

#### Crear Usuario

1. Accede a `Login.html`
2. Haz clic en "Crear cuenta"
3. Ingresa nombre de usuario y contraseña
4. El sistema crea automáticamente un usuario con rol `'user'`

#### Crear Administrador

El primer administrador se crea solo al iniciar la BBDD
administrador es nombre admin contraseña admin

#### Flujo de Trabajo del Usuario

1. **Login** → Autenticación
2. **Crear Post** → Escribe título y contenido (se censura automáticamente)
3. **Ver Posts** → Navega por publicaciones propias o de otros
4. **Comentar** → Interactúa en posts (censura automática)
5. **Configuración** → Cambia tema visual o cierra sesión

#### Panel de Administrador

Los administradores tienen acceso a:
- **Ver Todos los Posts**: Listado completo con opción de eliminar cualquier post
- **Censura**: Gestionar diccionario de palabras prohibidas

---

## Temas Visuales

### Tema Neón (Por defecto)
- Fondo oscuro con gradientes
- Efectos glow en azul (#45f3ff) y rosa (#ff2770)
- Animaciones suaves
- Ideal para ambientes nocturnos

### Tema Empresarial
- Paleta neutra (blancos, grises, marrones)
- Diseño profesional sin efectos luminosos
- Tonos cálidos tipo madera (#8d6e63)
- Ideal para uso corporativo

**Cambio de Tema**: Configuración → Selector de Tema

---

##  Ejemplos de Código

### Crear Usuario Programáticamente
```php
require_once 'Models/User.php';
require_once 'PDO/UserPDO.php';

$usuario = new User(0, "juan_perez", password_hash("mi_password", PASSWORD_DEFAULT));
$userPDO = new UserPDO();

try {
    $userPDO->insertUser($usuario);
    echo "Usuario creado exitosamente";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Verificar y Censurar Contenido
```php
require_once 'Models/Censura.php';
require_once 'PDO/CensuraPDO.php';

$censura = new Censura();
$censuraPDO = new CensuraPDO();

// Cargar palabras prohibidas desde BD
$palabras = $censuraPDO->obtenerPalabras();
$censura->setPalabrasProhibidas($palabras);

// Censurar texto
$textoOriginal = "Este contenido tiene palabras no permitidas";
$textoCensurado = $censura->censurarTexto($textoOriginal);

// Detectar palabras
$encontradas = $censura->detectarPalabrasProhibidas($textoOriginal);
if (!empty($encontradas)) {
    echo "Palabras detectadas: " . implode(", ", $encontradas);
}

```


</div>




