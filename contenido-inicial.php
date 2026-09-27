<?php
/**
 * Contenido inicial tomado del sitio actual. Se carga una sola vez al activar
 * el plugin, y solo en los tipos que todavía no tienen entradas.
 */

defined( 'ABSPATH' ) || exit;

function vcnt_cargar_contenido_inicial() {
	if ( get_option( 'vcnt_contenido_inicial' ) ) {
		return;
	}

	$obras = array(
		array(
			'title'   => 'Dust: de un sentido temporal',
			'excerpt' => 'Experimentación, diseño e implementación de una técnica inspirada en el tubo de Kundt para visualizar ondas estacionarias de sonido en un tubo de acrílico.',
			'meta'    => array( 'vcnt_anio' => 2025, 'vcnt_contexto' => 'Mónica Bate', 'vcnt_rol' => 'Desarrollo técnico', 'vcnt_medios' => 'Max, cadena electroacústica', 'vcnt_enlace' => '' ),
			'order'   => 0,
		),
		array(
			'title'   => 'Tecnologías para tocar lo invisible',
			'excerpt' => 'Programación de un instrumento virtual para web y de la instalación interactiva de la exposición en el Museo de Arte Popular Americano.',
			'meta'    => array( 'vcnt_anio' => 2025, 'vcnt_contexto' => 'Museo de Arte Popular Americano', 'vcnt_rol' => 'Instrumento web e instalación', 'vcnt_medios' => 'Max, RNBO, p5.js, JavaScript', 'vcnt_enlace' => 'https://github.com/notoriousvcnt/mapa-silbatos' ),
			'order'   => 1,
		),
		array(
			'title'   => 'Micelio',
			'excerpt' => 'Diseño y desarrollo técnico para una instalación lumínico-sonora expuesta en el zócalo del Palacio Pereira.',
			'meta'    => array( 'vcnt_anio' => 2025, 'vcnt_contexto' => 'Francisca Alsúa · Palacio Pereira', 'vcnt_rol' => 'Diseño y desarrollo técnico', 'vcnt_medios' => 'Arduino, C++, electrónica', 'vcnt_enlace' => 'https://github.com/notoriousvcnt/micelio' ),
			'order'   => 2,
		),
		array(
			'title'   => 'Woven Memory',
			'excerpt' => 'Implementación de la cadena de procesamiento de audio para la exposición en Londres 38.',
			'meta'    => array( 'vcnt_anio' => 2023, 'vcnt_contexto' => 'Soledad Muñoz · Londres 38', 'vcnt_rol' => 'Programación de audio', 'vcnt_medios' => 'Pure Data, Raspberry Pi', 'vcnt_enlace' => 'https://github.com/notoriousvcnt/woven-memory' ),
			'order'   => 0,
		),
		array(
			'title'   => 'Árboles Ciudadanos',
			'excerpt' => 'Desarrollo técnico de un dispositivo especulativo de captura de señales arbóreas para composición generativa, en un proyecto que recoge relatos en torno a árboles.',
			'meta'    => array( 'vcnt_anio' => 2022, 'vcnt_anio_fin' => 2023, 'vcnt_contexto' => 'Proyecto de relatos en torno a árboles', 'vcnt_rol' => 'Desarrollo técnico y composición generativa', 'vcnt_medios' => 'Arduino, Max, VCV Rack', 'vcnt_enlace' => '' ),
			'order'   => 0,
		),
		array(
			'title'   => 'MIM: Módulo Voz y Comunicación',
			'name'    => 'modulo-voz-y-comunicacion',
			'excerpt' => 'Programación de múltiples cadenas de efectos de sonido para el módulo «Voz y Comunicación» del Museo Interactivo Mirador, junto a Felipe Weason, por encargo de No Ordinary Things (N.O.T.).',
			'meta'    => array( 'vcnt_anio' => 2021, 'vcnt_contexto' => 'Museo Interactivo Mirador · con Felipe Weason', 'vcnt_rol' => 'Programación de audio', 'vcnt_medios' => 'Pure Data', 'vcnt_enlace' => '' ),
			'order'   => 0,
		),
	);

	$publicaciones = array(
		array(
			'title' => 'Resurfacing an Enactive Approach for Instrument Design: The case of the Tangible Granular Device',
			'meta'  => array( 'vcnt_anio' => 2024, 'vcnt_revista' => 'Proceedings of NIME', 'vcnt_autores' => 'V. E. Espinoza, J. Jaimovich', 'vcnt_url' => 'https://zenodo.org/records/13904798' ),
		),
		array(
			'title' => 'Perceptual Recognition of Sound Trajectories in Space',
			'meta'  => array( 'vcnt_anio' => 2021, 'vcnt_revista' => 'Computer Music Journal 45(1): 39–54', 'vcnt_autores' => 'F. Schumacher, V. Espinoza, F. Mardones, R. Vergara, A. Aránguiz, V. Aguilera', 'vcnt_url' => 'https://doi.org/10.1162/comj_a_00593' ),
		),
	);

	$cursos = array(
		array( 'title' => 'Programación para Ingeniería en Sonido', 'meta' => array( 'vcnt_detalle' => 'Octave / MATLAB' ) ),
		array( 'title' => 'Sistemas Interactivos / Instrumentos Musicales Digitales', 'meta' => array( 'vcnt_detalle' => 'Max, microcontroladores' ) ),
		array( 'title' => 'Proyecto Integrador de Señales y Sistemas Sonoros', 'meta' => array( 'vcnt_detalle' => '4º año' ) ),
		array( 'title' => 'Núcleo de Artes Sonoras', 'meta' => array( 'vcnt_detalle' => '[cargo]' ) ),
	);

	$lotes = array(
		'obra'        => $obras,
		'publicacion' => $publicaciones,
		'curso'       => $cursos,
	);

	foreach ( $lotes as $tipo => $items ) {
		$existentes = get_posts(
			array(
				'post_type'      => $tipo,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( $existentes ) {
			continue;
		}
		foreach ( $items as $i => $item ) {
			$id = wp_insert_post(
				array(
					'post_type'    => $tipo,
					'post_status'  => 'publish',
					'post_title'   => $item['title'],
					'post_name'    => isset( $item['name'] ) ? $item['name'] : '',
					'post_excerpt' => isset( $item['excerpt'] ) ? $item['excerpt'] : '',
					'post_content' => isset( $item['excerpt'] ) ? '<!-- wp:paragraph --><p>' . esc_html( $item['excerpt'] ) . '</p><!-- /wp:paragraph -->' : '',
					'menu_order'   => isset( $item['order'] ) ? $item['order'] : $i,
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				foreach ( $item['meta'] as $k => $v ) {
					if ( '' !== $v ) {
						update_post_meta( $id, $k, $v );
					}
				}
			}
		}
	}

	update_option( 'vcnt_contenido_inicial', 1, false );
}
