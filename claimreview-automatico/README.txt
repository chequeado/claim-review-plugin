=== ClaimReview Automático ===
Contributors: chequeado
Tags: schema, fact-checking, claim-review, verificación, chequeo
Requires at least: 4.7
Tested up to: 6.7
Requires PHP: 5.4
Recommended PHP: 7.2+
Stable tag: 1.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Genera automáticamente el esquema ClaimReview para artículos de verificación y fact-checking.

== Descripción ==
ClaimReview Automático es un plugin que facilita la implementación del esquema ClaimReview en sitios de verificación de datos y fact-checking. Este esquema es un estándar utilizado por Google, Facebook, Bing y otras plataformas para identificar y mostrar contenido verificado. E

Este plugin soporta únicamente sitios en español. El plugin transforma de manera automática los títulos de verificaciones en descripciones de la desinformación. Ej: "Es falso que la tierra es plana" se transforma en "La tierra es plana". Esto se debe a que ClaimReview requiere una descripción de la desinformación. Esta función se puede desactivar en la pantalla de ajustes de ClaimReview y además los títulos generados automáticamente se pueden corregir en la pantalla de edición de cada posteo para corregir errores.

El plugin fue desarrollado por Chequeado y permite a los verificadores de datos:
* Generar automáticamente el esquema ClaimReview basado en el título y contenido del artículo
* Configurar taxonomías personalizadas para identificar chequeos y verificaciones
* Definir calificaciones personalizadas para las verificaciones
* Editar manualmente la frase verificada si es necesario
* Implementar el esquema sin necesidad de conocimientos técnicos

== Instalación ==
1. Sube el plugin 'claim-review-plugin' al directorio `/wp-content/plugins/` o instala vía admin de WordPress
2. Activa el plugin a través del menú 'Plugins' en WordPress
3. Ve a 'Ajustes > ClaimReview Automático' para configurar el plugin
4. Configura:
   * El tipo de entrada para los artículos: qué tipo de entrada se usa para tus verificaciones?
   * Las taxonomías para identificar chequeos y/o verificaciones (etiquetas, categorías, etc.)
   * Los slugs que identifican chequeos y/o verificaciones
   * El nombre y logo de tu organización
   * Las calificaciones que utilizas
   * Configuración extra  

== Configuración ==
1. **Tipo de Entrada**: Selecciona el tipo de contenido donde se publicarán los chequeos
2. **Taxonomías**: Configura qué taxonomías se usarán para identificar:
   * Artículos de fact-checking del discurso público
   * Verificaciones de desinformación
   * Calificaciones
3. **Slugs**: Define los términos que identifican:
   * Chequeos de fact-checking
   * Verificaciones de desinformación
4. **Organización**: Configura:
   * Nombre de la organización
   * URL del logo
5. **Calificaciones**: La escala de calificaciones que se utilizara para los artículos de fact-checking y desinformaciones, ordenado desde el peor valor (falso) hacia el mejor (verdadero)
6. **Autor de desinformación por defecto**: valor predeterminado para usar como autores u origen de las desinformación (ej: "Posteos virales")

== Uso ==
1. Publica un artículo del tipo de contenido configurado
2. Asigna las taxonomías correspondientes (fact-check o verificación)
3. Asigna una calificación
4. El schema ClaimReview se generará automáticamente
5. Opcionalmente, puedes editar la frase verificada en la caja "Descripción para Claim Review"

== Frequently Asked Questions ==

= ¿Cómo funciona la generación automática del claim? =
Para fact-checks, el plugin toma el texto después de ":", "|" o "," en el título.
Para verificaciones, transforma las negaciones en afirmaciones.

= ¿Puedo editar el claim generado automáticamente? =
Sí, cada artículo tiene una caja de edición donde puedes modificar el claim si el generado automáticamente no es correcto.

= ¿Qué pasa si no asigno una calificación? =
El schema no se generará hasta que asignes una calificación válida al artículo.

== Changelog ==

= 1.0 =
* Primera versión pública del plugin
* Opciones de configuración para desactivar generación automática del título
* Opción para editar manualmente el claim
* Soporte para verificaciones de desinformación
* Interfaz de configuración 
* Mejor manejo de calificaciones personalizadas

== Contribute ==
El código fuente está disponible en GitHub: https://github.com/chequeado/claim-review-plugin
