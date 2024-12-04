<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galería Interactiva</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff; /* Fondo blanco para toda la página */
    }

    /* === Sección de la galería === */
    .gallery-section {
      width: 100%;
      max-width: 1200px;
      margin: 50px auto;
      padding: 0 20px;
      background-color: #fff; /* Fondo blanco para la galería */
      border-radius: 10px;
    }

    /* === Estilos para el título de la sección Galería === */
    .section-title {
      text-align: center;
      font-size: 2.5em;
      font-weight: bold;
      color: #07244a; /* Azul */
      margin: 30px 0 20px;
      font-family: 'Arial', sans-serif;
      position: relative;
    }

    /* Línea decorativa debajo del título */
    .section-title::after {
      content: "";
      display: block;
      width: 80px;
      height: 4px;
      background-color: #e70022; /* Rojo */
      margin: 10px auto;
      border-radius: 2px;
    }

    .gallery-header p {
      font-size: 1.2rem;
      color: #777;
      margin-bottom: 30px;
      text-align: center;
    }

    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      grid-auto-rows: 250px;
      padding: 20px;
    }

    .cuadro {
      position: relative;
      overflow: hidden;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    /* Estilo para el cuadro con dos imágenes */
    .cuadro img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: opacity 0.3s ease, transform 0.3s ease;
      border-radius: 15px;
    }

    /* Imagen por defecto */
    .cuadro .image-default {
      opacity: 1;
    }

    /* Imagen cuando pasa el cursor */
    .cuadro .image-hover {
      opacity: 0;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      transition: opacity 0.3s ease, box-shadow 0.3s ease;
    }

    /* Efecto hover: mostrar la imagen hover con sombra */
    .cuadro:hover {
      transform: scale(1.05);
    }

    .cuadro:hover .image-default {
      opacity: 0;
    }

    .cuadro:hover .image-hover {
      opacity: 1;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Sombra sutil en la imagen de hover */
    }

    /* Texto de especialización centrado */
    .specialty-name {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-size: 1.5rem;
      text-transform: uppercase;
      letter-spacing: 2px;
      opacity: 0;
      transition: opacity 0.3s ease;
      background-color: rgba(0, 0, 0, 0.5); /* Fondo semi-transparente */
      padding: 10px;
      border-radius: 5px;
    }

    .cuadro:hover .specialty-name {
      opacity: 1;
    }

    /* Estilos responsivos */
    @media (max-width: 768px) {
      .section-title {
        font-size: 2rem;
      }

      .gallery-header p {
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>

  <div class="gallery-section">
    <!-- Título de la sección Galería -->
    <div class="section-title">
      Galería
    </div>

    <div class="gallery-header">
      <p>Explora nuestras especialidades a través de imágenes dinámicas.</p>
    </div>

    <div class="gallery-grid">
      <!-- Cuadro de Electrónica -->
      <div class="cuadro">
        <img src="../../imagenes/electronica2.jpeg" alt="Electrónica" class="image-default">
        <img src="../../imagenes/electricidad1 (2).jpeg" alt="Electrónica Hover" class="image-hover">
        <div class="specialty-name">Electrónica</div>
      </div>

      <!-- Cuadro de Electricidad -->
      <div class="cuadro">
        <img src="../../imagenes/electricidad1 (2).jpeg" alt="Electricidad" class="image-default">
        <img src="../../imagenes/electricidad1_hover.jpeg" alt="Electricidad Hover" class="image-hover">
        <div class="specialty-name">Electricidad</div>
      </div>

      <!-- Cuadro de Mecánica Automotriz -->
      <div class="cuadro">
        <img src="../../imagenes/mecanica_automotriz.png" alt="Mecánica Automotriz" class="image-default">
        <img src="../../imagenes/mecanica_automotriz_hover.jpeg" alt="Mecánica Automotriz Hover" class="image-hover">
        <div class="specialty-name">Mecánica Automotriz</div>
      </div>

      <!-- Cuadro de Diseño Gráfico -->
      <div class="cuadro">
        <img src="../../imagenes/diseno_grafico.jpg" alt="Diseño Gráfico" class="image-default">
        <img src="../../imagenes/diseno_grafico_hover.jpg" alt="Diseño Gráfico Hover" class="image-hover">
        <div class="specialty-name">Diseño Gráfico</div>
      </div>

      <!-- Cuadro de Programación -->
      <div class="cuadro">
        <img src="../../imagenes/programacion.jpg" alt="Programación" class="image-default">
        <img src="../../imagenes/programacion_hover.jpg" alt="Programación Hover" class="image-hover">
        <div class="specialty-name">Programación</div>
      </div>

      <!-- Cuadro de Marketing Digital -->
      <div class="cuadro">
        <img src="../../imagenes/marketing_digital.jpg" alt="Marketing Digital" class="image-default">
        <img src="../../imagenes/marketing_digital_hover.jpg" alt="Marketing Digital Hover" class="image-hover">
        <div class="specialty-name">Marketing Digital</div>
      </div>
    </div>
  </div>

</body>
</html>
