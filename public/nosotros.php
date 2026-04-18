<?php
// public/nosotros.php
session_start();
require_once '../includes/header.php';
?>

<main class="about-container">
    <section class="about-hero">
        <div class="about-hero-content">
            <h1>Nuestra Esencia</h1>
            <p>Donde la ciencia de la piel se encuentra con el arte del color.</p>
        </div>
    </section>

    <section class="about-story">
        <div class="story-image">
            <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?q=80&w=1000&auto=format&fit=crop" alt="Maquillaje de alta gama">
        </div>
        <div class="story-text">
            <span>Desde 2026</span>
            <h2>Nacida de la pasión por la luz natural</h2>
            <p>
                Glow & Beauty no nació en un laboratorio, sino en la búsqueda de productos que no ocultaran la piel, sino que la celebraran. Creemos que el maquillaje debe ser una herramienta de empoderamiento, no una máscara.
            </p>
            <p>
                Cada producto en nuestro catálogo ha sido seleccionado bajo estándares rigurosos de calidad, priorizando ingredientes que cuidan tu rostro mientras resaltan tu belleza única.
            </p>
        </div>
    </section>

    <section class="about-values">
        <div class="value-item">
            <i class="fas fa-leaf"></i>
            <h3>Ética y Conciencia</h3>
            <p>El 100% de nuestro catálogo es Cruelty Free. Creemos firmemente en una belleza libre de crueldad animal.</p>
        </div>
        <div class="value-item">
            <i class="fas fa-gem"></i>
            <h3>Calidad Premium</h3>
            <p>Trabajamos exclusivamente con marcas que utilizan pigmentos de alta fidelidad y fórmulas de larga duración.</p>
        </div>
        <div class="value-item">
            <i class="fas fa-magic"></i>
            <h3>Inclusividad</h3>
            <p>Diseñamos nuestra selección para todos los tonos y tipos de piel, porque la luz no tiene etiquetas.</p>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>