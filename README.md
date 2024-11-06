# ClaimReview Automático

## Overview
**Contributors:** chequeado  
**Tags:** schema, fact-checking, claim-review, verificación, chequeo  
**Requires WordPress:** 4.7  
**Tested up to:** 6.4  
**Requires PHP:** 5.4  
**Recommended PHP:** 7.2+  
**Stable tag:** 2.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Genera automáticamente el esquema ClaimReview para artículos de verificación y fact-checking.

## Description
ClaimReview Automático es un plugin que facilita la implementación del esquema ClaimReview en sitios de verificación de datos y fact-checking. Este esquema es un estándar utilizado por Google, Facebook, Bing y otras plataformas para identificar y mostrar contenido verificado.

El plugin fue desarrollado por Chequeado y permite a los verificadores de datos:
* Generar automáticamente el esquema ClaimReview basado en el título y contenido del artículo
* Configurar taxonomías personalizadas para identificar chequeos y verificaciones
* Definir calificaciones personalizadas para las verificaciones
* Editar manualmente la frase verificada si es necesario
* Implementar el esquema sin necesidad de conocimientos técnicos

### Características Principales
* Generación automática del schema ClaimReview
* Soporte para fact-checks y verificaciones de desinformación
* Configuración flexible de taxonomías y calificaciones
* Interfaz para edición manual de claims
* Compatible con el formato de títulos más común en fact-checking
* Integración con las calificaciones existentes del medio

## Installation
1. Sube el plugin 'claim-review-plugin' al directorio `/wp-content/plugins/` o instala vía admin de WordPress
2. Activa el plugin a través del menú 'Plugins' en WordPress
3. Ve a 'Ajustes > ClaimReview Automático' para configurar el plugin
4. Configura:
   * El tipo de entrada para los artículos
   * Las taxonomías para identificar chequeos y verificaciones (etiquetas, categorías, etc.)
   * Los slugs que identifican chequeos y verificaciones
   * El nombre y logo de tu organización
   * Las calificaciones que utilizas

## Configuration
1. **Tipo de Entrada**: Selecciona el tipo de contenido donde se publicarán los chequeos
2. **Taxonomías**: Configura qué taxonomías se usarán para identificar:
   * Artículos de fact-checking
   * Verificaciones de desinformación
   * Calificaciones
3. **Slugs**: Define los términos que identifican:
   * Chequeos de fact-checking
   * Verificaciones de desinformación
4. **Organización**: Configura:
   * Nombre de la organización
   * URL del logo
   * Valor predeterminado para usar como autores u origen de las desinformación (ej: "Posteos virales")

## Usage
1. Publica un artículo del tipo de contenido configurado
2. Asigna las taxonomías correspondientes (fact-check o verificación)
3. Asigna una calificación
4. El schema ClaimReview se generará automáticamente
5. Opcionalmente, puedes editar la frase verificada en la caja "Descripción para Claim Review"

## FAQ

### ¿Cómo funciona la generación automática del claim?
Para fact-checks, el plugin toma el texto después de ":", "|" o "," en el título.
Para verificaciones, transforma las negaciones en afirmaciones.

### ¿Puedo editar el claim generado automáticamente?
Sí, cada artículo tiene una caja de edición donde puedes modificar el claim si el generado automáticamente no es correcto.

### ¿Qué pasa si no asigno una calificación?
El schema no se generará hasta que asignes una calificación válida al artículo.

## Changelog

### 2.0
* Agregada opción para editar manualmente el claim
* Soporte mejorado para verificaciones de desinformación
* Interfaz de configuración renovada
* Mejor manejo de calificaciones personalizadas

### 1.0
* Primera versión pública del plugin

## Contribute
El código fuente está disponible en GitHub: https://github.com/chequeado/claim-review-plugin
