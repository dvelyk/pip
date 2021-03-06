<?php
/**
 * Generic WordPress query helpers.
 *
 * @package joist
 */

/**
 * Search SQL filter for matching against post title only.
 *
 * @link    http://wordpress.stackexchange.com/a/11826/1685
 *
 * @param string   $search The (unused) search string.
 * @param WP_Query $wp_query The existing query.
 */
function joist_search_by_title( $search, $wp_query ) {
	if ( ! is_admin() ) {
		return $search;
	}

	$q = $wp_query->query_vars;

	if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
		global $wpdb;

		$terms = $wp_query->query_vars['search_terms'];
		$wrap  = empty( $wp_query->query_vars['exact'] ) ? '%' : '';

		if ( ! is_array( $terms ) ) {
			$terms = [ $terms ];
		}

		$search = array();

		foreach ( $terms as $term ) {
			$search[] = $wpdb->prepare(
				"$wpdb->posts.post_title LIKE %s",
				$wrap . $wpdb->esc_like( $term ) . $wrap
			);
		}

		if ( ! is_user_logged_in() ) {
			$search[] = "$wpdb->posts.post_password = ''";
		}

		$search = ' AND ' . implode( ' AND ', $search );
	}

	return $search;
}

add_filter( 'posts_search', 'joist_search_by_title', 10, 2 );


function joist_limit_month_archive_to_year( $sql_where, $args ) {
	global $wpdb;

	if ( 'monthly' === $args['type'] && ! empty( $args['year'] ) ) {
		$sql_where .= $wpdb->prepare(
			' AND YEAR(post_date) = %s',
			$args['year']
		);
	}

	return $sql_where;
}

add_filter( 'getarchives_where', 'joist_limit_month_archive_to_year', 10, 2 );
