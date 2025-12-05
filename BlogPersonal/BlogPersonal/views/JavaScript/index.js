// Cargar tema guardado desde localStorage
const themaCss = localStorage.getItem('themaCss') || '../CSS/Index-neon.css';
document.getElementById('themeStylesheet').setAttribute('href', themaCss);
// Actualizar el selector para que muestre el tema actual
const themeSelector = document.getElementById('themeSelector');
if (themeSelector) {
  themeSelector.value = themaCss;
}

// Mostrar secciones según botón
function showSection(id) {
  document.querySelectorAll('.section').forEach(sec => sec.classList.add('hidden'));
  document.getElementById(id).classList.remove('hidden');
}

document.getElementById('btnCrear').addEventListener('click', () => showSection('crearSection'));
document.getElementById('btnBuscar').addEventListener('click', () => showSection('buscarSection'));
document.getElementById('btnPerfil').addEventListener('click', () => showSection('perfilSection'));

// 🔥 Configuración: mostrar sección y cargar datos del usuario
document.getElementById('btnConfig').addEventListener('click', () => {
  showSection('configSection');
  cargarDatosUsuario();
});

// 🛡️ Censura y control de roles: redirigir a página de administración (solo admin)
document.addEventListener('DOMContentLoaded', async () => {
  try {
    const response = await fetch('../../Controllers/UserController.php?action=perfil');
    const data = await response.json();
    
    // Guardar datos del usuario en localStorage para comentarios
    if (data.id_user) {
      localStorage.setItem('usuarioId', data.id_user);
    }
    if (data.nombre) {
      localStorage.setItem('usuarioNombre', data.nombre);
    }
    if (data.rol === 'admin') {
      localStorage.setItem('esAdmin', 'true');
    } else {
      localStorage.setItem('esAdmin', 'false');
    }
    
    // Si el usuario es admin
    if (data.rol === 'admin') {
      // Mostrar botones de admin
      const btnCensura = document.getElementById('btnCensura');
      const btnTodosPosts = document.getElementById('btnTodosPosts');
      
      if (btnCensura) {
        btnCensura.style.display = 'block';
        btnCensura.addEventListener('click', () => {
          showSection('censuraSection');
          cargarPalabrasCensura();
        });
      }
      
      if (btnTodosPosts) {
        btnTodosPosts.style.display = 'block';
        btnTodosPosts.addEventListener('click', () => {
          showSection('todosPostsSection');
          cargarTodosPosts();
        });
      }

      // Ocultar botones de usuario normal
      const btnCrear = document.getElementById('btnCrear');
      const btnPerfil = document.getElementById('btnPerfil');
      
      if (btnCrear) btnCrear.style.display = 'none';
      if (btnPerfil) btnPerfil.style.display = 'none';
    } else {
      // Usuario normal: ocultar botones de admin
      const btnCensura = document.getElementById('btnCensura');
      const btnTodosPosts = document.getElementById('btnTodosPosts');
      
      if (btnCensura) btnCensura.style.display = 'none';
      if (btnTodosPosts) btnTodosPosts.style.display = 'none';
    }
  } catch (err) {
    console.error('Error verificando rol de admin:', err);
  }
});

// ----------------------
// Función para cargar datos del usuario
// ----------------------
async function cargarDatosUsuario() {
  try {
    const response = await fetch('../../Controllers/UserController.php?action=perfil');
    const data = await response.json();

    const perfilInfo = document.getElementById('perfilInfo');

    if (data.error) {
      perfilInfo.innerHTML = "<p>No hay sesión activa.</p>";
    } else {
      perfilInfo.innerHTML = `
        <p><strong>Nombre:</strong> ${data.nombre}</p>
        <p><strong>Registrado el:</strong> ${data.Fecha_Registro}</p>
      `;
    }
  } catch (err) {
    console.error("Error cargando datos de usuario:", err);
  }
}

// Crear post
document.getElementById('postForm').addEventListener('submit', async e => {
  e.preventDefault();
  const formData = new FormData(e.target);

  const response = await fetch('../../Controllers/PostController.php', { 
    method: 'POST', 
    body: formData 
  });

  const result = await response.text();
  document.getElementById('alertContainer').innerHTML = result;
  
  // Limpiar campos después de publicar
  document.getElementById('titulo').value = '';
  document.getElementById('contenido').value = '';
});

// Buscar posts de otros usuarios
async function buscarPostsPorUsuario(nombre) {
  const formData = new FormData();
  formData.append('action', 'buscar_post');
  formData.append('usuario', nombre);

  const response = await fetch('../../Controllers/PostController.php', {
    method: 'POST',
    body: formData
  });

  return await response.json();
}

document.getElementById('searchForm').addEventListener('submit', async e => {
  e.preventDefault();
  const nombre = document.getElementById('usuario').value;
  const posts = await buscarPostsPorUsuario(nombre);

  const resultsDiv = document.getElementById('searchResults');
  resultsDiv.innerHTML = '';

  if (!posts || posts.length === 0) {
    resultsDiv.innerHTML = '<p>No se encontraron posts para ese usuario.</p>';
  } else {
    posts.forEach(post => {
      const postDiv = document.createElement('div');
      postDiv.classList.add('post');
      postDiv.innerHTML = `
        <h3>${post.titulo}</h3>
        <p>${post.contenido}</p>
        <small>Autor: ${post.nombre} | Fecha: ${post.fecha_creacion}</small>
      `;
      
      // Agregar sección de comentarios
      postDiv.appendChild(createCommentsSection(post.id_post));
      
      resultsDiv.appendChild(postDiv);
    });
  }
});

// Mis posts
async function loadPerfilPosts() {
  const response = await fetch('../../Controllers/PostController.php?action=mis_posts');
  const posts = await response.json();
  const container = document.getElementById('perfilPosts');
  container.innerHTML = '';

  if (!posts || posts.length === 0) {
    container.innerHTML = '<p>No tienes posts todavía.</p>';
  } else {
    posts.forEach(post => {
      const postDiv = document.createElement('div');
      postDiv.classList.add('post');
      postDiv.innerHTML = `
        <h3>${post.titulo}</h3>
        <p>${post.contenido}</p>
        <small>Publicado el ${post.fecha_creacion}</small>
        <div class="post-actions">
          <button class="btn-small btn-delete" data-id="${post.id_post}">🗑️ Eliminar</button>
        </div>
      `;
      
      // Agregar sección de comentarios
      postDiv.appendChild(createCommentsSection(post.id_post));
      
      container.appendChild(postDiv);
    });

    // Agregar listeners a botones de eliminar
    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', (e) => confirmarEliminar(e.target.dataset.id));
    });
  }
}

document.getElementById('btnPerfil').addEventListener('click', loadPerfilPosts);

// Confirmar eliminación
async function confirmarEliminar(id_post) {
  if (!confirm('⚠️ ¿Estás seguro de que quieres eliminar este post? Esta acción no se puede deshacer.')) {
    return;
  }

  const formData = new FormData();
  formData.append('action', 'eliminar_post');
  formData.append('id_post', id_post);

  try {
    const response = await fetch('../../Controllers/PostController.php', {
      method: 'POST',
      body: formData
    });
    const result = await response.json();

    if (result.success) {
      alert(result.success);
      loadPerfilPosts(); // Recargar posts
    } else {
      alert('Error: ' + result.error);
    }
  } catch (err) {
    console.error('Error eliminando post:', err);
    alert('Error al eliminar el post');
  }
}

// Cargar todos los posts (solo para admin)
async function cargarTodosPosts() {
  try {
    const response = await fetch('../../Controllers/PostController.php?action=todos_posts');
    const posts = await response.json();
    const container = document.getElementById('todosPostsList');
    container.innerHTML = '';

    if (!posts || posts.length === 0) {
      container.innerHTML = '<p>No hay posts disponibles.</p>';
    } else {
      posts.forEach(post => {
        const postDiv = document.createElement('div');
        postDiv.classList.add('post');
        postDiv.innerHTML = `
          <h3>${post.titulo}</h3>
          <p>${post.contenido}</p>
          <small>Autor: <strong>${post.nombre}</strong> | Publicado el ${post.fecha_creacion}</small>
          <div class="post-actions">
            <button class="btn-small btn-delete" data-id="${post.id_post}">🗑️ Eliminar</button>
          </div>
        `;
        
        // Agregar sección de comentarios
        postDiv.appendChild(createCommentsSection(post.id_post));
        
        container.appendChild(postDiv);
      });

      // Agregar listeners a botones de eliminar
      document.querySelectorAll('#todosPostsList .btn-delete').forEach(btn => {
        btn.addEventListener('click', (e) => confirmarEliminar(e.target.dataset.id));
      });
    }
  } catch (err) {
    console.error('Error cargando todos los posts:', err);
    document.getElementById('todosPostsList').innerHTML = '<p>Error al cargar los posts</p>';
  }
}

document.getElementById('themeSelector').addEventListener('change', e => {
  const cssFile = e.target.value;
  document.getElementById('themeStylesheet').setAttribute('href', cssFile);
  localStorage.setItem('themaCss', cssFile); // Guardar tema seleccionado
});

// ========================================
// Funciones para Censura (solo admin)
// ========================================

async function cargarPalabrasCensura() {
  try {
    const response = await fetch('../../Controllers/ComentarioController.php?action=obtener_palabras');
    const data = await response.json();

    if (data.palabras) {
      const container = document.getElementById('palabrasListCensura');
      container.innerHTML = '';

      if (data.palabras.length === 0) {
        container.innerHTML = '<p>No hay palabras prohibidas configuradas.</p>';
      } else {
        data.palabras.forEach(palabra => {
          const div = document.createElement('div');
          div.innerHTML = `
            <div style="margin-bottom: 8px; font-weight: 600;">${palabra}</div>
            <button onclick="removerPalabraCensura('${palabra}')" style="background: #ef5350; color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 11px;">Eliminar</button>
          `;
          container.appendChild(div);
        });
      }

      document.getElementById('totalPalabrasCensura').textContent = data.palabras.length;
    }
  } catch (err) {
    console.error('Error cargando palabras:', err);
  }
}

async function agregarPalabraCensura() {
  const palabra = document.getElementById('nuevaPalabraCensura').value;

  if (!palabra.trim()) {
    mostrarAlertaCensura('Por favor, ingresa una palabra', 'error');
    return;
  }

  try {
    const formData = new FormData();
    formData.append('action', 'agregar_palabra');
    formData.append('palabra', palabra);

    const response = await fetch('../../Controllers/ComentarioController.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.success) {
      mostrarAlertaCensura(data.success, 'success');
      document.getElementById('nuevaPalabraCensura').value = '';
      cargarPalabrasCensura();
    } else if (data.error) {
      mostrarAlertaCensura(data.error, 'error');
    }
  } catch (err) {
    console.error('Error agregando palabra:', err);
    mostrarAlertaCensura('Error al agregar la palabra', 'error');
  }
}

async function removerPalabraCensura(palabra) {
  if (!confirm(`¿Estás seguro de que quieres remover la palabra "${palabra}"?`)) {
    return;
  }

  try {
    const formData = new FormData();
    formData.append('action', 'remover_palabra');
    formData.append('palabra', palabra);

    const response = await fetch('../../Controllers/ComentarioController.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.success) {
      mostrarAlertaCensura(data.success, 'success');
      cargarPalabrasCensura();
    } else if (data.error) {
      mostrarAlertaCensura(data.error, 'error');
    }
  } catch (err) {
    console.error('Error removiendo palabra:', err);
    mostrarAlertaCensura('Error al remover la palabra', 'error');
  }
}

function mostrarAlertaCensura(mensaje, tipo) {
  const container = document.getElementById('censuraAlerts');
  const div = document.createElement('div');
  div.className = `alert alert-${tipo}`;
  div.textContent = mensaje;
  container.appendChild(div);

  setTimeout(() => {
    div.remove();
  }, 4000);
}

// Permitir agregar palabra con Enter en la sección de censura
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('nuevaPalabraCensura');
  if (input) {
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        agregarPalabraCensura();
      }
    });
  }
});

// Logout
document.getElementById('btnLogout').addEventListener('click', () => {
  if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
    window.location.href = '../../Controllers/UserController.php?action=logout';
  }
});

// COMENTARIOS

// Toggle comentarios expandible/contraible
function toggleComments(headerElement) {
  const toggle = headerElement.querySelector('.comments-toggle');
  const container = headerElement.parentElement.querySelector('.comments-container');
  
  toggle.classList.toggle('open');
  container.classList.toggle('hidden');
}

 // Crear plantilla de comentarios para un post
function createCommentsSection(postId) {
  const template = document.getElementById('commentsTemplate');
  const clone = template.content.cloneNode(true);
  
  // Extraer la sección del fragment
  const section = clone.querySelector('.comments-section');
  section.dataset.postId = postId;
  
  // Obtener referencias a los elementos DENTRO de la sección
  const header = section.querySelector('.comments-header');
  const btnCommentar = section.querySelector('.btn-small');
  const textarea = section.querySelector('.comment-input');
  const commentsContainer = section.querySelector('.comments-container');
  const commentsList = section.querySelector('.comments-list');
  
  // Agregar el evento para cargar comentarios al expandir
  header.addEventListener('click', function() {
    toggleComments(this);
    // Cargar comentarios cuando se expande
    if (!commentsContainer.classList.contains('hidden') && commentsList.innerHTML === '') {
      cargarComentarios(postId, commentsList);
    }
  });

  // Agregar evento al botón de comentar
  btnCommentar.addEventListener('click', () => {
    enviarComentario(postId, textarea, commentsList);
  });

  textarea.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && e.ctrlKey) {
      enviarComentario(postId, textarea, commentsList);
    }
  });

  // Retornar el fragment para que se inserte correctamente en el DOM
  // Las referencias a section, commentsList, etc. se mantienen válidas
  return clone;
}

// Cargar comentarios de un post
async function cargarComentarios(postId, container) {
  try {
    const formData = new FormData();
    formData.append('action', 'obtener_comentarios');
    formData.append('id_post', postId);

    const response = await fetch('../../Controllers/ComentarioController.php', {
      method: 'POST',
      body: formData
    });

    const comentarios = await response.json();
    
    container.innerHTML = '';

    if (Array.isArray(comentarios) && comentarios.length > 0) {
      comentarios.forEach(com => {
        const div = document.createElement('div');
        div.className = 'comment-item';
        
        const btnDelete = localStorage.getItem('usuarioId') === String(com.id_user) || localStorage.getItem('esAdmin') === 'true'
          ? `<button class="comment-delete" onclick="eliminarComentario(${com.id_comentario}, ${postId}, this)">✕</button>`
          : '';

        div.innerHTML = `
          <div class="comment-header">
            <div>
              <span class="comment-user">${com.usuario}</span>
              <span class="comment-date">${new Date(com.fecha_creacion).toLocaleString('es-ES')}</span>
            </div>
            ${btnDelete}
          </div>
          <div class="comment-content">${com.contenido}</div>
        `;
        
        container.appendChild(div);
      });
    } else {
      container.innerHTML = '<p style="color: #71767b; font-size: 12px;">No hay comentarios aún.</p>';
    }

    // Actualizar contador en el header del template
    const commentHeader = container.parentElement.querySelector('.comments-header');
    if (commentHeader) {
      commentHeader.querySelector('.comments-count').textContent = comentarios.length || 0;
    }

  } catch (err) {
    console.error('Error cargando comentarios:', err);
    container.innerHTML = '<p style="color: red; font-size: 12px;">Error al cargar comentarios</p>';
    
    // Si hay error, poner contador en 0
    const commentHeader = container.parentElement.querySelector('.comments-header');
    if (commentHeader) {
      commentHeader.querySelector('.comments-count').textContent = 0;
    }
  }
}


 // Enviar un comentario nuevo
async function enviarComentario(postId, textarea, commentsList) {
  const contenido = textarea.value.trim();

  if (!contenido) {
    alert('El comentario no puede estar vacío');
    return;
  }

  try {
    const formData = new FormData();
    formData.append('action', 'crear_comentario');
    formData.append('id_post', postId);
    formData.append('contenido', contenido);

    const response = await fetch('../../Controllers/ComentarioController.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.success) {
      textarea.value = '';
      cargarComentarios(postId, commentsList);
    } else if (data.error) {
      alert('Error: ' + data.error);
    }
  } catch (err) {
    console.error('Error enviando comentario:', err);
    alert('Error al enviar el comentario');
  }
}

 // Eliminar un comentario
async function eliminarComentario(idComentario, postId, buttonElement) {
  if (!confirm('¿Estás seguro de que quieres eliminar este comentario?')) {
    return;
  }

  try {
    const formData = new FormData();
    formData.append('action', 'eliminar_comentario');
    formData.append('id_comentario', idComentario);

    const response = await fetch('../../Controllers/ComentarioController.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.success) {
      buttonElement.closest('.comment-item').remove();
    } else if (data.error) {
      alert('Error: ' + data.error);
    }
  } catch (err) {
    console.error('Error eliminando comentario:', err);
    alert('Error al eliminar el comentario');
  }
}
