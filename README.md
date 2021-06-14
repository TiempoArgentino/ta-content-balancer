
# Introducción

Post Balancer es un plugin para Wordpress que permite recopilar información
acerca de la navegación del usuario en nuestro sitio web, para luego usarla en
queries personalizadas que resultan en posts de interés para el usuario.

# Requisitos

- Wordpress >= 5.0
- PHP >= 8

# Instalación y activación

- Descargar el último release y extraerlo en `wp-content/plugins/posts-balancer`.
- Ir al panel de plugins del sitio (`/wp-admin/plugins.php`) y activar el plugin `Balancer`

# Funcionamiento


### ¿Qué información guarda?
El balanceador recopila las ids de los `terms` de los `posts` que visita el usuario
en base a ciertas variables preestablecidas por el administrador del sitio desde
el panel de configuración del plugin (ver **Configuración**).

### ¿Dónde se guardan estos datos?

Dependiendo del ***tipo de usuario*** que se encuentra navegando el sitio, el
balanceador decide si la información se guarda en la base de datos, o en el
navegador del usuario a través del `localStorage`.

Estos ***tipos de usuarios*** son:

- **Usuario logueado**: Los datos se guardan en la base de datos
- **Usuario anónimo**: Son aquellos que no se encuentran logueados. Los datos
se guardan en el `localStorage` del navegador que se encuentren usando.
- **Usuario personalizado**: Son usuarios logueados que tienen una serie
de preferencias preestablecidas. El balanceador no recopila información para
estos usuarios.

Configuración
-------------------------
El plugin permite modificar sus variables a través del panel de administración
de Wordpress en **Ajustes => Balancer**. En esta pantalla se pueden modificar
los siguientes datos:

#### Post Type y Taxonomies

Establece sobre qué `post type` se trabaja, y que datos de estos recopila (`taxonomies`).

- **Post type to balance**: El `post type` al que pertenecen los posts de los cuales
se quieren guardar los datos.
- **Days ago**: Qué tan antiguo puede ser un post para que se tenga en cuenta.
- **Place taxonomy**: `taxonomy` que indica el **lugar** del artículo.
- **Topics taxonomy**: `taxonomy` que indica los **temas** de un artículo.
- **Tags taxonomy**: `taxonomy` que indica las **etiquetas** de un artículo.
- **Autor taxonomy**: `taxonomy` que indica los **autores** de un artículo.
- **Editorial taxonomy**: `taxonomy` que indica la **sección** de un artículo.


#### Porcentajes

Configuración que indica la cantidad de posts que se buscan en base a ciertos
criterios. La sumatoria de estos valores debe ser igual a 100.

Estos criterios son:

- **More Views Percent**: En base a la cantidad de visitas.
- **Percent User**: En base a las preferencias del usuario.
- **Editorial Percent**: Los mas recientes que no entren en las 2 categorías anteriores.


# ¿Cómo utilizar los datos recopilados?

Los datos balanceados del usuario actual se pueden acceder desde `PHP` y
`Javascript`. Estos se pueden utilizar para hacer queries que devuelvan posts en
base a estas preferencias.

Los datos de los **Usuarios Anónimos** solo se pueden acceder desde el navegador.

## PHP API

Herramientas para acceder a los datos de usuarios logueados y configuración
del balanceador.

### Preferencias del usuario:

El balanceador completa los datos del usuario durante el action `wp_head`. Luego
de ese action, los datos se pueden acceder utilizando el método estático
`Post_Balancer_User_Data::get_current_user_data`, que devuelve un `array` con
los siguientes datos:

```php
<?php
/**
*   @property mixed[] {
*       @property mixed[] info {
*           @property int[] posts - Ids de los últimos posts visitados por el usuario
*           @property int[] cats - Ids de las secciones guardadas
*           @property int[] tags - Ids de las etiquetas guardadas
*           @property int[] authors - Ids de los autores guardados
*           @property int[] topics - Ids de los temas guardados
*           @property int[] locations - Ids de los lugares guardados
*       }
*   }
*/
$user_preferences = Post_Balancer_User_Data::get_current_user_data();
```

### Porcentajes:

Se puede acceder a los porcentajes utilizando el método
`Posts_Balancer_Personalize::get_percentages`

```php
<?php
/**
*   @property int[] {
*       @property int views
*       @property int user
*       @property int editorial
*   }
*/
$percentages = Posts_Balancer_Personalize::get_percentages();
```

## Javascript API

Herramientas para acceder desde el navegador a los datos de usuarios logeados y
anónimos, y configuración del balanceador.

### Preferencias del usuario:

Desde el navegador se puede acceder a la variable global `postsBalancer`.
Utilizando el método `loadPreferences` se pueden acceder a las preferencias del
usuario, ya sea logueado, anónimo o con personalización. Este método devuelve un
`promise` que se resuelve una vez se hayan cargado los datos correctamente.

Estas preferencias tienen la misma estructura que en `PHP`, pero en formato `JSON`.

Ejemplo:
```js
/**
*   @property {object} {
*       @property {object} info {
*           @property {int[]} posts
*           @property {int[]} cats
*           @property {int[]} tags
*           @property {int[]} authors
*           @property {int[]} topics
*           @property {int[]} locations
*       }
*   }
*/
const userPreference = await window.postsBalancer.loadPreferences();
```

### Porcentajes:

Se puede acceder a los porcentajes utilizando la función
`window.postsBalancerData.percentages`

```js
/**
*   @property {object} {
*       @property {int} views
*       @property {int} user
*       @property {int} editorial
*   }
*/
const percentages = window.postsBalancerData.percentages;
```
