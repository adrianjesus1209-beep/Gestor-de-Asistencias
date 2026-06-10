<?php
/**
 * UNEFA - SISTEMA DE ASISTENCIA QR
 * Punto de entrada para hosting compartido (InfinityFree)
 */

// Definimos una constante para indicar que estamos en la raíz
define('APP_INDEX_ROOT_WRAPPER', true);

// Requerimos el index principal que está dentro de public
require_once __DIR__ . '/public/index.php';
