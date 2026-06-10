# Sistema de Gestión de Asistencias mediante Códigos QR (UNEFA)

## Descripción del Proyecto
Este sistema automatiza el control de asistencia estudiantil de la UNEFA utilizando códigos QR únicos para cada estudiante. El objetivo es optimizar el registro de entrada, garantizar la veracidad de los datos y facilitar la generación de reportes automáticos basados en bloques horarios.

## Objetivos Principales
* **Automatización**: Eliminar el registro manual de asistencias.
* **Seguridad**: Vinculación de identidades únicas mediante hashes y códigos QR.
* **Precisión**: Validación en tiempo real del cumplimiento de horarios y lapsos de tolerancia.
* **Modularidad**: Arquitectura dividida por departamentos para un desarrollo escalable.

## Equipo de Trabajo

### Líder Global de Proyecto
* **Adrian Bello** ✓

### Departamento: Frontend
* **Líder de Departamento:** Johnfrank Romero ✓
* **Sub-líder:** Fernando Ruiz ✓
1. Horangel Barrios ✓
2. Francibel Serven ✓
3. Rutbelis Córdoba ✓
4. Saray Contreras ✓
5. Keinserlin Lezama ✓

### Departamento: Base de Datos
* **Líder de Departamento:** Erian Nuñez ✓
* **Sub-líder:** Jesús Pacheco ✓
1. Gabriel González ✓
2. José Rojas ✓
3. Osmeli Sangroni ✓
4. Brigitte Rodríguez ✓
5. Wisbaldo Chirinos ✓

### Departamento: Backend
* **Líder de Departamento:** Gabriel Cobos ✓
* **Sub-líder:** Cristian Trosel ✓
1. Gregori Arrieta ✓
2. Susana Acevedo ✓
3. Gilbert Labrador ✓
4. José Losada ✓
5. Claudia Colorado ✓

## Reglas de Negocio del Sistema (QR)

Para garantizar la seguridad y el orden del sistema, se han definido las siguientes reglas oficiales:

1. El QR no contiene información personal, solo un código secreto (Hash).
2. El estudiante no puede generar su propio QR, el sistema lo genera automáticamente al registrarse.
3. Cada estudiante tiene un solo QR, es único e intransferible.
4. Si un estudiante pierde su QR, no puede obtener uno nuevo automáticamente; debe solicitar la regeneración al administrador.
5. El QR es válido únicamente mientras el estudiante esté en estado Activo. Si se retira, su QR se bloquea automáticamente.

## Estructura de Ramas del Repositorio
* **main**: Rama de producción (Restringida).
* **frontend**: Desarrollos de la capa de interfaz y experiencia de usuario.
* **backend**: Desarrollos de la lógica de servidor y API.
* **Base-de-datos**: Gestión de esquemas, migraciones y scripts SQL.

---
© 2026 UNEFA - Excelencia Educativa Abierta al Pueblo.
