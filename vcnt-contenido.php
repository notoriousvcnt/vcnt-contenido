<?php
/**
 * Plugin Name:       vcnt — Contenido
 * Description:       Tipos de contenido para vcnt.cl: Trabajos, Publicaciones y Cursos, con sus campos. Independiente del tema, para no perder el contenido si cambias de diseño.
 * Version:           1.0.3
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Vicente Espinoza
 * License:           GPL-2.0-or-later
 * Text Domain:       vcnt
 * Update URI:        https://github.com/notoriousvcnt/vcnt-contenido
 */

defined( 'ABSPATH' ) || exit;

$vcnt_puc = __DIR__ . '/lib/plugin-update-checker/plugin-update-checker.php';
if ( is_readable( $vcnt_puc ) ) {
	require_once $vcnt_puc;
	$vcnt_upd = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/notoriousvcnt/vcnt-contenido/',
		__FILE__,
		'vcnt-contenido'
	);
	if ( defined( 'VCNT_GITHUB_TOKEN' ) ) {
		$vcnt_upd->setAuthentication( VCNT_GITHUB_TOKEN );
	}
}

define( 'VCNT_CONTENIDO_FILE', __FILE__ );

/**
 * Definición de los campos de cada tipo de contenido.
 * type: text | url | number | textarea
 */
function vcnt_campos() {
	return array(
		'obra'        => array(
			'vcnt_anio'     => array( 'label' => 'Año (inicio)', 'type' => 'number', 'help' => 'Ej: 2022. Ordena las obras y ubica el marcador en la línea de tiempo.' ),
			'vcnt_anio_fin' => array( 'label' => 'Año (fin, opcional)', 'type' => 'number', 'help' => 'Solo si la obra duró más de un año. Ej: 2023.' ),
			'vcnt_contexto' => array( 'label' => 'Artista / contexto', 'type' => 'text', 'help' => 'Ej: Mónica Bate · Palacio Pereira' ),
			'vcnt_rol'      => array( 'label' => 'Rol', 'type' => 'text', 'help' => 'Ej: Desarrollo técnico' ),
			'vcnt_medios'   => array( 'label' => 'Medios', 'type' => 'text', 'help' => 'Separados por coma. Ej: Max, RNBO, p5.js' ),
			'vcnt_enlace'   => array( 'label' => 'Enlace externo (opcional)', 'type' => 'url', 'help' => 'Repositorio o registro. Si está vacío, se enlaza la página de la obra.' ),
		),
		'publicacion' => array(
			'vcnt_anio'    => array( 'label' => 'Año', 'type' => 'number', 'help' => '' ),
			'vcnt_revista' => array( 'label' => 'Revista / congreso', 'type' => 'text', 'help' => 'Ej: Computer Music Journal 45(1): 39–54' ),
			'vcnt_autores' => array( 'label' => 'Autores', 'type' => 'text', 'help' => '' ),
			'vcnt_url'     => array( 'label' => 'URL o DOI', 'type' => 'url', 'help' => '' ),
		),
		'curso'       => array(
			'vcnt_detalle' => array( 'label' => 'Detalle', 'type' => 'text', 'help' => 'Texto corto a la derecha. Ej: Octave / MATLAB' ),
		),
	);
}

/* ------------------------------------------------------------------ */
/* Tipos de contenido                                                  */
/* ------------------------------------------------------------------ */

function vcnt_registrar_tipos() {
	register_post_type(
		'obra',
		array(
			'labels'        => array(
				'name'          => 'Trabajos',
				'singular_name' => 'Trabajo',
				'add_new'       => 'Añadir trabajo',
				'add_new_item'  => 'Añadir trabajo',
				'edit_item'     => 'Editar trabajo',
				'all_items'     => 'Todos los trabajos',
			),
			'public'        => true,
			'has_archive'   => false,
			'rewrite'       => array( 'slug' => 'obras' ),
			'menu_icon'     => 'dashicons-format-audio',
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes' ),
			'show_in_rest'  => true,
			'capability_type' => 'page',
			'map_meta_cap'    => true,
		)
	);

	register_post_type(
		'publicacion',
		array(
			'labels'        => array(
				'name'          => 'Publicaciones',
				'singular_name' => 'Publicación',
				'add_new'       => 'Añadir publicación',
				'add_new_item'  => 'Añadir publicación',
				'edit_item'     => 'Editar publicación',
			),
			'public'             => false,
			'show_ui'            => true,
			'menu_icon'          => 'dashicons-book-alt',
			'menu_position'      => 6,
			'supports'           => array( 'title', 'custom-fields' ),
			'show_in_rest'       => true,
			'capability_type'    => 'page',
			'map_meta_cap'       => true,
		)
	);

	register_post_type(
		'curso',
		array(
			'labels'        => array(
				'name'          => 'Docencia',
				'singular_name' => 'Curso',
				'add_new'       => 'Añadir curso',
				'add_new_item'  => 'Añadir curso',
				'edit_item'     => 'Editar curso',
			),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-welcome-learn-more',
			'menu_position' => 7,
			'supports'      => array( 'title', 'page-attributes', 'custom-fields' ),
			'show_in_rest'  => true,
			'capability_type' => 'page',
			'map_meta_cap'    => true,
		)
	);

	foreach ( vcnt_campos() as $tipo => $campos ) {
		foreach ( $campos as $clave => $c ) {
			register_post_meta(
				$tipo,
				$clave,
				array(
					'type'              => 'number' === $c['type'] ? 'number' : 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'url' === $c['type'] ? 'esc_url_raw' : ( 'number' === $c['type'] ? 'vcnt_sanitizar_numero' : 'sanitize_text_field' ),
					'auth_callback'     => function ( $allowed, $meta_key, $object_id ) {
						return current_user_can( 'edit_post', $object_id );
					},
				)
			);
		}
	}
}
add_action( 'init', 'vcnt_registrar_tipos' );

function vcnt_sanitizar_numero( $v ) {
	return ( '' === $v || null === $v ) ? '' : (float) $v;
}

/* ------------------------------------------------------------------ */
/* Caja de campos (funciona en el editor de bloques y en el clásico)   */
/* ------------------------------------------------------------------ */

function vcnt_agregar_cajas() {
	foreach ( array_keys( vcnt_campos() ) as $tipo ) {
		add_meta_box( 'vcnt_campos', 'Datos', 'vcnt_render_caja', $tipo, 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'vcnt_agregar_cajas' );

function vcnt_render_caja( $post ) {
	$campos = vcnt_campos()[ $post->post_type ];
	wp_nonce_field( 'vcnt_guardar_campos', 'vcnt_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( $campos as $clave => $c ) {
		$valor = get_post_meta( $post->ID, $clave, true );
		$tipo  = in_array( $c['type'], array( 'number', 'url' ), true ) ? $c['type'] : 'text';
		$step  = 'number' === $tipo ? ' step="1" min="1990" max="2100"' : '';
		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input class="regular-text" type="%3$s" id="%1$s" name="%1$s" value="%4$s"%5$s>%6$s</td></tr>',
			esc_attr( $clave ),
			esc_html( $c['label'] ),
			esc_attr( $tipo ),
			esc_attr( $valor ),
			$step, // Literal fijo, sin datos del usuario.
			$c['help'] ? '<p class="description">' . esc_html( $c['help'] ) . '</p>' : ''
		);
	}
	echo '</tbody></table>';
	if ( 'obra' === $post->post_type ) {
		echo '<p class="description">La imagen destacada es la imagen del trabajo. El extracto es la descripción corta que aparece en la ventana Trabajos; el contenido completo va en la página de la obra.</p>';
	}
}

function vcnt_guardar_campos( $post_id, $post ) {
	if ( ! isset( $_POST['vcnt_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vcnt_nonce'] ) ), 'vcnt_guardar_campos' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['post_ID'] ) || (int) $_POST['post_ID'] !== (int) $post_id ) {
		return;
	}
	$campos = vcnt_campos();
	if ( ! isset( $campos[ $post->post_type ] ) ) {
		return;
	}
	foreach ( $campos[ $post->post_type ] as $clave => $c ) {
		if ( ! isset( $_POST[ $clave ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $clave ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- se sanea abajo.
		if ( 'url' === $c['type'] ) {
			$v = esc_url_raw( $raw );
		} elseif ( 'number' === $c['type'] ) {
			$v = vcnt_sanitizar_numero( sanitize_text_field( $raw ) );
		} else {
			$v = sanitize_text_field( $raw );
		}
		if ( '' === $v ) {
			delete_post_meta( $post_id, $clave );
		} else {
			update_post_meta( $post_id, $clave, $v );
		}
	}
}
add_action( 'save_post', 'vcnt_guardar_campos', 10, 2 );

/* Columnas en el listado de obras */
add_filter(
	'manage_obra_posts_columns',
	function ( $cols ) {
		$nuevo = array();
		foreach ( $cols as $k => $v ) {
			$nuevo[ $k ] = $v;
			if ( 'title' === $k ) {
				$nuevo['vcnt_anio'] = 'Año';
				$nuevo['vcnt_rol']  = 'Rol';
			}
		}
		unset( $nuevo['date'] );
		return $nuevo;
	}
);
add_action(
	'manage_obra_posts_custom_column',
	function ( $col, $id ) {
		if ( 'vcnt_anio' === $col ) {
			echo esc_html( vcnt_anio_texto( $id ) );
		} elseif ( 'vcnt_rol' === $col ) {
			echo esc_html( get_post_meta( $id, 'vcnt_rol', true ) );
		}
	},
	10,
	2
);

/* ------------------------------------------------------------------ */
/* Ayudantes que usa el tema                                           */
/* ------------------------------------------------------------------ */

/** Texto del año: "2025" o "2022–23". */
function vcnt_anio_texto( $post_id ) {
	$a = get_post_meta( $post_id, 'vcnt_anio', true );
	$b = get_post_meta( $post_id, 'vcnt_anio_fin', true );
	if ( '' === $a ) {
		return '';
	}
	$a = (int) $a;
	if ( '' !== $b && (int) $b > $a ) {
		$b = (int) $b;
		return $a . '–' . ( intdiv( $a, 100 ) === intdiv( $b, 100 ) ? substr( (string) $b, -2 ) : $b );
	}
	return (string) $a;
}

/** Punto medio del periodo, para ubicar el marcador. */
function vcnt_anio_medio( $post_id ) {
	$a = (float) get_post_meta( $post_id, 'vcnt_anio', true );
	$b = get_post_meta( $post_id, 'vcnt_anio_fin', true );
	return ( '' !== $b && (float) $b > $a ) ? ( $a + (float) $b ) / 2 : $a;
}

/** Ordena por año sin ocultar lo que no tenga año. */
function vcnt_meta_anio() {
	return array(
		'relation' => 'OR',
		'anio'     => array(
			'key'     => 'vcnt_anio',
			'compare' => 'EXISTS',
			'type'    => 'NUMERIC',
		),
		array(
			'key'     => 'vcnt_anio',
			'compare' => 'NOT EXISTS',
		),
	);
}

/** Consultas ordenadas. */
function vcnt_obras() {
	return get_posts(
		array(
			'post_type'      => 'obra',
			'posts_per_page' => -1,
			'meta_query'     => vcnt_meta_anio(), // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'        => array(
				'anio'       => 'DESC',
				'menu_order' => 'ASC',
			),
		)
	);
}
function vcnt_publicaciones() {
	return get_posts(
		array(
			'post_type'      => 'publicacion',
			'posts_per_page' => -1,
			'meta_query'     => vcnt_meta_anio(), // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'        => array( 'anio' => 'DESC' ),
		)
	);
}
function vcnt_cursos() {
	return get_posts(
		array(
			'post_type'      => 'curso',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
}

/* ------------------------------------------------------------------ */
/* Redirecciones desde el sitio anterior                               */
/* ------------------------------------------------------------------ */

/**
 * Si una dirección antigua da 404, redirige (301):
 * - páginas antiguas del menú → la ventana correspondiente de la portada;
 * - entradas antiguas cuyo slug coincide con una obra → la página de la obra.
 * Solo actúa cuando la dirección ya no existe, así que no interfiere mientras
 * las entradas antiguas sigan publicadas.
 */
function vcnt_redirecciones() {
	if ( ! is_404() ) {
		return;
	}
	$ruta = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH ), '/' );
	if ( '' === $ruta ) {
		return;
	}
	$paginas = apply_filters(
		'vcnt_redirecciones_paginas',
		array(
			'portafolio-v2'            => '/#obras',
			'portafolio'               => '/#obras',
			'publicaciones-academicas' => '/#publicaciones',
			'contacto'                 => '/#contacto',
		)
	);
	if ( isset( $paginas[ $ruta ] ) ) {
		wp_safe_redirect( home_url( $paginas[ $ruta ] ), 301 );
		exit;
	}
	$slug = sanitize_title( basename( $ruta ) );
	$obra = get_page_by_path( $slug, OBJECT, 'obra' );
	if ( $obra && 'publish' === $obra->post_status ) {
		wp_safe_redirect( get_permalink( $obra ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'vcnt_redirecciones' );

/* ------------------------------------------------------------------ */
/* Activación: contenido inicial (solo si está vacío)                  */
/* ------------------------------------------------------------------ */

function vcnt_activar() {
	vcnt_registrar_tipos();
	require_once __DIR__ . '/contenido-inicial.php';
	vcnt_cargar_contenido_inicial();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vcnt_activar' );

register_deactivation_hook(
	__FILE__,
	function () {
		flush_rewrite_rules();
	}
);
