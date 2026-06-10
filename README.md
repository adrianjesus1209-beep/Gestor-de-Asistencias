# Sistema de Gestión de Asistencias mediante Códigos QR (UNEFA)

## Descripción del Proyecto
Este sistema automatiza el control de asistencia estudiantil de la UNEFA utilizando códigos QR únicos para cada estudiante. El objetivo es optimizar el registro de entrada, garantizar la veracidad de los datos y facilitar la generación de reportes automáticos basados en bloques horarios.

## Objetivos Principales
* Automatización: Eliminar el registro manual de asistencias.
* Seguridad: Vinculación de identidades únicas mediante hashes y códigos QR.
* Precisión: Validación en tiempo real del cumplimiento de horarios y lapsos de tolerancia.
* Modularidad: Arquitectura dividida por departamentos para un desarrollo escalable.

## Equipo de Trabajo

### Lider De Todos Los Departamentos
* **Adrian Bello** ✓

### Departamento: Frontend
* **Lider de departamento:** Johnfrank Romero ✓
* **Sub-líder:** Fernando Ruiz ✓
1. Horangel Barrios ✓
2. Francibel Serven ✓
3. Rutbelis Córdoba ✓
4. Saray Contreras ✓

### Departamento: Base de Datos
* **Lider de departamento:** Erian Nuñez ✓
* **Sub-líder:** Jesús Pacheco ✓
1. Keinserlin Lezama ✓
2. Gabriel González ✓
3. José Rojas ✓
4. Claudia Colorado ✓

### Departamento: Backend
* **Lider de departamento:** Gabriel Cobos ✓
* **Sub-líder:** Cristian Trosel ✓
1. Gregory Arrieta ✓
2. Susana Acevedo ✓
3. Gilbert Labrador ✓
4. José Losada ✓

### Departamento: Documentación
* **Lider de departamento:** Wisbaldo Chirinos ✓
1. Brigitte Rodríguez ✓
2. Osmeli Sangroni ✓

## Reglas de Negocio del Sistema (QR)

Para garantizar la seguridad y el orden del sistema, se han definido las siguientes reglas oficiales:

1. El QR no contiene información personal, solo un código secreto (Hash).
2. El estudiante no puede generar su propio QR, el sistema lo genera automáticamente al registrarse.
3. Cada estudiante tiene un solo QR, es único e intransferible.
4. Si un estudiante pierde su QR, no puede obtener uno nuevo automáticamente; debe solicitar la regeneración al administrador.
5. El QR es válido únicamente mientras el estudiante esté en estado Activo. Si se retira, su QR se bloquea automáticamente.

## Estructura del Proyecto (MVC)
* main: Rama principal controlada por el líder del proyecto.
* frontend: Desarrollos de la capa de presentación.
* backend: Desarrollos de la lógica de servidor.
* db: Gestión de esquemas y scripts SQL.
* docs: Documentación y especificaciones.

---
© 2026 UNEFA - Excelencia Educativa Abierta al Pueblo.
