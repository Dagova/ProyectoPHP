// Sistema de notificaciones para Login y Registro
// Detecta parámetros en URL y muestra toasts

document.addEventListener('DOMContentLoaded', function() {
  // Obtener parámetros de la URL
  const urlParams = new URLSearchParams(window.location.search);
  
  // Verificar login
  const loginStatus = urlParams.get('login');
  if (loginStatus === 'fail') {
    showToast('❌ Usuario o contraseña incorrectos', 'error');
  } else if (loginStatus === 'ok') {
    showToast('✅ Login exitoso', 'success');
  }
  
  // Verificar registro
  const registroStatus = urlParams.get('registro');
  if (registroStatus === 'ok') {
    showToast('✅ Usuario registrado correctamente', 'success');
  } else if (registroStatus === 'fail') {
    showToast('❌ Error al registrar. El nombre de usuario ya existe', 'error');
  }
  
  // Verificar logout
  const logoutStatus = urlParams.get('logout');
  if (logoutStatus === '1') {
    showToast('👋 Sesión cerrada correctamente', 'success');
  }
  
  // Limpiar URL después de mostrar el mensaje
  if (loginStatus || registroStatus || logoutStatus) {
    // Esperar un momento antes de limpiar la URL para que el usuario vea el mensaje
    setTimeout(() => {
      window.history.replaceState({}, document.title, window.location.pathname);
    }, 100);
  }
});

// Función para mostrar notificaciones toast
function showToast(message, type = 'info') {
  // Crear contenedor si no existe
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  
  // Crear toast
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;
  
  // Agregar al contenedor
  container.appendChild(toast);
  
  // Animación de entrada
  setTimeout(() => {
    toast.style.opacity = '1';
    toast.style.transform = 'translateX(0)';
  }, 10);
  
  // Remover después de 4 segundos
  setTimeout(() => {
    toast.classList.add('hide');
    setTimeout(() => {
      toast.remove();
    }, 500);
  }, 4000);
}
