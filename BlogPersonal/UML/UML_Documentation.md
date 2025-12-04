# Diagrama UML - BlogPersonal

Este diagrama muestra la arquitectura completa del proyecto BlogPersonal con todas las clases, sus relaciones y dependencias.

## Diagrama Visual

![UML Class Diagram](./UML_Diagram.png)

## Código Mermaid del Diagrama

```mermaid
classDiagram
    %% ===========================
    %% MODELS LAYER
    %% ===========================
    class User {
        -int id_user
        -string nombre
        -string password
        -string fecha_registro
        -string rol
        +__construct(int, string, string, string, string)
        +getIdUser() int
        +getNombre() string
        +getPassword() string
        +getFechaRegistro() string
        +getRol() string
        +isAdmin() bool
    }

    class Post {
        -int id_post
        -string titulo
        -string contenido
        -string fecha_creacion
        -int id_user
        +__construct(int, string, string, string, int)
        +getIdPost() int
        +getTitulo() string
        +getContenido() string
        +getFechaCreacion() string
        +getIdUser() int
    }

    class Comentario {
        -int id_comentarios
        -int id_post
        -int id_user
        -string contenido
        -string fecha_creacion
        -string nombre_usuario
        +__construct(int, int, int, string, string, string)
        +getIdComentario() int
        +getIdPost() int
        +getIdUser() int
        +getContenido() string
        +getFechaCreacion() string
        +getNombreUsuario() string
    }

    class Censura {
        -array palabrasProhibidas
        -string replacementChar
        +__construct()
        +setPalabrasProhibidas(array) void
        +getPalabrasProhibidas() array
        +agregarPalabraProhibida(string) void
        +removerPalabraProhibida(string) void
        +detectarPalabrasProhibidas(string) array
        +censurarTexto(string) string
        +esSeguro(string) bool
        +setReplacementChar(string) void
    }

    %% ===========================
    %% DATABASE CONNECTION
    %% ===========================
    class Conexion {
        -PDO pdo
        +getConexion() PDO
    }

    %% ===========================
    %% PDO LAYER (Data Access)
    %% ===========================
    class UserPDO {
        -PDO pdo
        +__construct()
        +insertUser(User) bool
        +fetchUserById(int) User
        +fetchUserByName(string) User
    }

    class PostPDO {
        -PDO pdo
        +__construct()
        +insertPost(Post) bool
        +fetchAllPosts() array
        +mis_posts(string) array
        +deletePost(int) bool
        +isOwner(int, int) bool
    }

    class ComentarioPDO {
        -PDO pdo
        +__construct()
        +insertComentario(Comentario) bool
        +obtenerComentariosPorPost(int) array
        +deleteComentario(int) bool
        +isOwner(int, int) bool
    }

    class CensuraPDO {
        -PDO pdo
        +__construct()
        +obtenerPalabras() array
        +agregarPalabra(string) bool
        +removerPalabra(string) bool
    }

    %% ===========================
    %% CONTROLLER LAYER (Business Logic)
    %% ===========================
    class UserController {
        -UserPDO userPDO
        +__construct()
        +registerUser(string, string) bool
        +login(string, string) bool
        +getPerfil() User
        +procesarLogin() void
        +procesarRegistro() void
        +logout() void
    }

    class PostController {
        -PostPDO postPDO
        -Censura censura
        -CensuraPDO censuraPDO
        +__construct()
        +crearPost(string, string, int) bool
        +verMisPosts() void
        +buscarPostsDeUsuario(string) void
        +eliminarPost(int, int, bool) bool
        +obtenerTodosPosts() void
    }

    class ComentarioController {
        -ComentarioPDO comentarioPDO
        -Censura censura
        -CensuraPDO censuraPDO
        +__construct()
        +crearComentario(string, int, int) bool
        +obtenerComentarios(int) void
        +eliminarComentario(int, int, bool) bool
        +obtenerPalabrasProhibidas() void
        +agregarPalabraProhibida(string) void
        +removerPalabraProhibida(string) void
    }

    %% ===========================
    %% RELATIONSHIPS - Models
    %% ===========================
    Post "many" --> "1" User : authored by
    Comentario "many" --> "1" Post : belongs to
    Comentario "many" --> "1" User : written by

    %% ===========================
    %% RELATIONSHIPS - PDO uses Conexion
    %% ===========================
    UserPDO ..> Conexion : uses
    PostPDO ..> Conexion : uses
    ComentarioPDO ..> Conexion : uses
    CensuraPDO ..> Conexion : uses

    %% ===========================
    %% RELATIONSHIPS - PDO uses Models
    %% ===========================
    UserPDO ..> User : creates/returns
    PostPDO ..> Post : creates/returns
    ComentarioPDO ..> Comentario : creates/returns

    %% ===========================
    %% RELATIONSHIPS - Controllers use PDO
    %% ===========================
    UserController o-- UserPDO : contains
    PostController o-- PostPDO : contains
    PostController o-- CensuraPDO : contains
    PostController o-- Censura : contains
    ComentarioController o-- ComentarioPDO : contains
    ComentarioController o-- CensuraPDO : contains
    ComentarioController o-- Censura : contains

    %% ===========================
    %% RELATIONSHIPS - Controllers use Models
    %% ===========================
    UserController ..> User : creates
    PostController ..> Post : creates
    ComentarioController ..> Comentario : creates
```

## Explicación de la Arquitectura

### Capas del Sistema

#### 1. **Models (Capa de Dominio)**
- **`User`**: Representa los usuarios del sistema con autenticación y roles
- **`Post`**: Representa las publicaciones del blog
- **`Comentario`**: Representa los comentarios en los posts
- **`Censura`**: Lógica de negocio para censurar palabras prohibidas

#### 2. **PDO (Capa de Acceso a Datos)**
- **`UserPDO`**: Operaciones CRUD para usuarios
- **`PostPDO`**: Operaciones CRUD para posts
- **`ComentarioPDO`**: Operaciones CRUD para comentarios
- **`CensuraPDO`**: Gestión de palabras prohibidas en BD
- **`Conexion`**: Singleton para la conexión a la base de datos

#### 3. **Controllers (Capa de Lógica de Negocio)**
- **`UserController`**: Gestión de autenticación, registro y perfil de usuarios
- **`PostController`**: Gestión de posts con censura automática
- **`ComentarioController`**: Gestión de comentarios y administración de palabras prohibidas

### Tipos de Relaciones

- **Asociación (→)**: Relaciones de negocio entre entidades del dominio
- **Dependencia (..)**: Una clase usa otra temporalmente (parámetros, valores de retorno)
- **Composición (o--)**: Una clase contiene otra como parte integral de su funcionamiento

### Flujo de Datos Típico

1. **Controllers** reciben peticiones HTTP desde las vistas
2. **Controllers** usan **PDO** para acceso a datos
3. **PDO** usa **Conexion** para interactuar con la Base de Datos
4. **PDO** crea y retorna objetos **Models**
5. **Controllers** procesan **Models** y aplican lógica de negocio
6. **Controllers** retornan respuestas JSON o redirecciones

### Características Principales

- **Separación de Responsabilidades**: Cada capa tiene un propósito específico
- **Sistema de Censura**: Integrado en PostController y ComentarioController
- **Gestión de Permisos**: Sistema de roles (user/admin) en User
- **Sesiones**: Manejo de autenticación mediante PHP sessions
- **Validación**: Sanitización de entradas con htmlspecialchars
- **Password Hashing**: Seguridad con password_hash y password_verify
