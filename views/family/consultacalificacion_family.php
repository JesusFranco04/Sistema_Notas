<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Consulta de Calificaciones | Sistema de Gestión UEBF</title>
<link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
<style>
    /* Estilos generales */
    body {
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f8f9fa;
      color: #333;
    }
    /* Estilos para el encabezado */
    header {
      background-color: #a2000e;
      color: #fff;
      padding: 20px;
      text-align: center;
    }
    header h1 {
      margin: 0;
      font-size: 32px;
    }
    /* Estilos para la barra de navegación */
    nav {
      background-color: #fff;
      padding: 10px 0;
      text-align: center;
      border-bottom: 2px solid #e9ecef;
    }
    nav a {
      color: #343a40;
      text-decoration: none;
      margin: 0 20px;
      font-size: 18px;
      position: relative;
      overflow: hidden;
    }
    nav a:hover {
      color: #e70014;
    }
    nav a::after {
      content: '';
      position: absolute;
      width: 0%;
      height: 2px;
      background-color:#e70014;
      bottom: 0;
      left: 50%;
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }
    nav a:hover::after,
    nav a.active::after {
      width: 100%;
    }
    /* Estilos para el contenido */
    section {
      padding: 40px;
      text-align: center;
    }
    section h2 {
      font-size: 28px;
      margin-bottom: 20px;
    }
    section p {
      font-size: 18px;
      line-height: 1.6;
    }
    /* Estilos para el pie de página */
    footer {
      background-color: #be0010;
      color: #fff;
      padding: 20px;
      text-align: center;
    }
    
    /* Estilos para dispositivos móviles */
    @media only screen and (max-width: 600px) {
      nav a {
        margin: 0 10px;
        font-size: 16px;
      }
      header h1 {
        font-size: 24px;
      }
      section {
        padding: 20px;
      }
      section h2 {
        font-size: 24px;
      }
      section p {
        font-size: 16px;
      }
    }
    
    /* Estilos para el botón de cerrar sesión */
    button {
      background-color: #ff0016;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 50px;
      transition: background-color 0.3s, transform 0.3s;
      cursor: pointer;
    }
    button:hover {
      background-color: #0056b3;
      transform: scale(1.1);
    }
</style>
</head>
<body>
<header>
  <h1>SISTEMA DE GESTIÓN "UEBF"</h1>
</header>
<nav style="padding: 10px;">
    <div style="display: flex; align-items: center;">
      <a href="http://localhost/sistema_notas/views/family/index_family.html">Inicio</a>
      <a href="http://localhost/sistema_notas/views/family/consultacalificacion_family.html">Consulta de Calificaciones</a>
      <a href="http://localhost/sistema_notas/views/family/preguntas_family.html">FAQ</a>
      <div style="margin-left: auto; margin-right: 20px;">
        <span style="font-size: 14px; color: #888;">Nombre Completo del Usuario</span>
        <span style="margin-left: 20px;"></span> <!-- Espacio adicional -->
        <button style="background-color: #e70014; color: #fff; border: none; padding: 10px 20px; border-radius: 50px;">Cerrar Sesión</button>
      </div>
    </div>
  </nav>
    
<section>
  <h2>¡Bienvenido!</h2>
  <p>Esta es el sistema de gestion para Calificaciones.</p>
</section>
<footer>
  <p>Derechos Reservados &copy; 2024</p>
</footer>
</body>
</html>