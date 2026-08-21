<?php
/**
 * Plugin Name: LGS Property Status Reconcile
 * Description: Keeps WordPress property posts in step with the booking API. Drafts properties that
 *              are no longer active in the booking system, and restores them if they come back.
 * Version:     1.0.0
 * Requires PHP: 7.4
 *
 * Install: upload to wp-content/mu-plugins/ (create the folder if it does not exist). Must-use
 * plugins load automatically — there is no activation step, and a plugin update cannot overwrite
 * this file. To remove it, delete the file and run:
 *     wp cron event delete lgs_prop_reconcile
 *
 * Before it does anything useful, fill in the TODO constants below.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Configuration — every TODO must be set to match this install.
 * ---------------------------------------------------------------------- */

/** Post type holding properties. Find it with: wp post-type list */
define( 'LGS_PROP_POST_TYPE', 'property' );                    // TODO

/** Post meta key holding the booking system's property ID. wp post meta list <ID> */
define( 'LGS_PROP_REMOTE_ID_META', '_property_remote_id' );    // TODO

/** API base URL, no trailing slash. */
define( 'LGS_PROP_API_BASE', 'https://api.example.com' );      // TODO

/** Path of the endpoint that lists properties. */
define( 'LGS_PROP_API_PATH', '/properties' );                  // TODO

/**
 * Auth. Store the credential in an option, never in this file — anything in wp-content is one
 * misconfigured server away from being served as plain text. Set it once with:
 *     wp option add lgs_prop_api_key 'THEKEY' --autoload=no
 */
define( 'LGS_PROP_API_KEY_OPTION', 'lgs_prop_api_key' );
define( 'LGS_PROP_AUTH_HEADER', 'Authorization' );             // TODO e.g. 'X-Api-Key'
define( 'LGS_PROP_AUTH_PREFIX', 'Bearer ' );                   // TODO '' if the key is sent bare

/** Field on each property object holding its ID. */
define( 'LGS_PROP_ID_FIELD', 'id' );                           // TODO

/**
 * Field holding the active/inactive flag, and the values that mean "active".
 * Leave LGS_PROP_STATUS_FIELD empty if the endpoint only ever returns active properties — then
 * presence in the response is what counts as active.
 */
define( 'LGS_PROP_STATUS_FIELD', 'status' );                   // TODO '' if there is no such field
define( 'LGS_PROP_ACTIVE_VALUES', 'active,live,published,1,true,enabled' );

/**
 * Pagination. Set LGS_PROP_PAGE_PARAM to '' if the endpoint returns everything in one response.
 * Getting this wrong is the one way this plugin can do damage, so the safety guard below exists.
 */
define( 'LGS_PROP_PAGE_PARAM', 'page' );                       // TODO '' for no pagination
define( 'LGS_PROP_PER_PAGE_PARAM', 'per_page' );               // TODO
define( 'LGS_PROP_PER_PAGE', 100 );
define( 'LGS_PROP_MAX_PAGES', 50 );

/**
 * Safety guard. If the API reports fewer active properties than this fraction of what is currently
 * published in WordPress, the run aborts and changes nothing. One truncated or half-failed API
 * response must never be able to unpublish the site.
 */
define( 'LGS_PROP_MIN_RATIO', 0.4 );

define( 'LGS_PROP_CRON_HOOK', 'lgs_prop_reconcile' );
define( 'LGS_PROP_DRAFTED_META', '_lgs_auto_drafted' );
define( 'LGS_PROP_LOG_OPTION', 'lgs_prop_reconcile_log' );
define( 'LGS_PROP_LAST_RUN_OPTION', 'lgs_prop_reconcile_last_run' );

/* -------------------------------------------------------------------------
 * Scheduling
 * ---------------------------------------------------------------------- */

add_action( 'init', function () {
	if ( ! wp_next_scheduled( LGS_PROP_CRON_HOOK ) ) {
		wp_schedule_event( time() + 300, 'hourly', LGS_PROP_CRON_HOOK );
	}
} );

add_action( LGS_PROP_CRON_HOOK, function () {
	lgs_prop_reconcile( false, false );
} );

/* -------------------------------------------------------------------------
 * Fetching the authoritative active set
 * ---------------------------------------------------------------------- */

/**
 * Returns an array of remote property IDs (as strings) that are active in the booking system,
 * or WP_Error on any failure. Never returns a partial set silently.
 *
 * Plug in your own fetch by returning a non-null array from the 'lgs_prop_active_ids' filter.
 *
 * @return string[]|WP_Error
 */
function lgs_prop_fetch_active_ids() {
	$override = apply_filters( 'lgs_prop_active_ids', null );
	if ( is_array( $override ) ) {
		return array_values( array_unique( array_map( 'strval', $override ) ) );
	}

	$key = (string) get_option( LGS_PROP_API_KEY_OPTION, '' );
	if ( '' === $key ) {
		return new WP_Error( 'lgs_prop_no_key', 'No API key in option ' . LGS_PROP_API_KEY_OPTION );
	}

	$args = array(
		'timeout' => 30,
		'headers' => array(
			LGS_PROP_AUTH_HEADER => LGS_PROP_AUTH_PREFIX . $key,
			'Accept'             => 'application/json',
		),
	);
	$args = apply_filters( 'lgs_prop_request_args', $args );

	$active        = array();
	$page          = 1;
	$page_was_full = false;
	$paginate      = '' !== LGS_PROP_PAGE_PARAM;

	do {
		$query = array();
		if ( $paginate ) {
			$query[ LGS_PROP_PAGE_PARAM ]     = $page;
			$query[ LGS_PROP_PER_PAGE_PARAM ] = LGS_PROP_PER_PAGE;
		}
		$query = apply_filters( 'lgs_prop_query_args', $query, $page );
		$url   = LGS_PROP_API_BASE . LGS_PROP_API_PATH;
		if ( $query ) {
			$url = add_query_arg( $query, $url );
		}

		$response = wp_remote_get( $url, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return new WP_Error( 'lgs_prop_http', sprintf( 'HTTP %d from %s', $code, $url ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( null === $body ) {
			return new WP_Error( 'lgs_prop_json', 'Response from ' . $url . ' was not valid JSON' );
		}

		$rows = lgs_prop_extract_rows( $body );
		if ( ! is_array( $rows ) ) {
			return new WP_Error( 'lgs_prop_shape', 'Could not find a list of properties in the response' );
		}

		$found_this_page = 0;
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) || ! isset( $row[ LGS_PROP_ID_FIELD ] ) ) {
				continue;
			}
			++$found_this_page;
			if ( lgs_prop_row_is_active( $row ) ) {
				$active[] = (string) $row[ LGS_PROP_ID_FIELD ];
			}
		}

		$page_was_full = ( $found_this_page >= LGS_PROP_PER_PAGE );
		++$page;
	} while ( $paginate && $page_was_full && $page <= LGS_PROP_MAX_PAGES );

	// Only a cap hit while pages were still coming back full means the config is wrong; a short
	// final page is the normal way to reach the end.
	if ( $paginate && $page_was_full && $page > LGS_PROP_MAX_PAGES ) {
		return new WP_Error( 'lgs_prop_pages', 'Hit LGS_PROP_MAX_PAGES with full pages still arriving — pagination config is probably wrong' );
	}

	return array_values( array_unique( $active ) );
}

/**
 * Finds the list of property objects in a decoded response, whether it is a bare array or wrapped
 * in one of the usual envelope keys.
 *
 * @param mixed $body Decoded JSON.
 * @return array|null
 */
function lgs_prop_extract_rows( $body ) {
	if ( ! is_array( $body ) ) {
		return null;
	}
	if ( isset( $body[0] ) ) {
		return $body;
	}
	foreach ( array( 'data', 'properties', 'results', 'items', 'records', 'rows' ) as $key ) {
		if ( isset( $body[ $key ] ) && is_array( $body[ $key ] ) ) {
			return $body[ $key ];
		}
	}
	return null;
}

/**
 * @param array $row One property object from the API.
 * @return bool
 */
function lgs_prop_row_is_active( array $row ) {
	if ( '' === LGS_PROP_STATUS_FIELD ) {
		return true; // Presence in the response is the signal.
	}
	if ( ! array_key_exists( LGS_PROP_STATUS_FIELD, $row ) ) {
		return true; // Field absent on this object — do not infer inactive from a missing field.
	}

	$value = $row[ LGS_PROP_STATUS_FIELD ];
	if ( is_bool( $value ) ) {
		return $value;
	}

	$value  = strtolower( trim( (string) $value ) );
	$active = array_map( 'trim', explode( ',', strtolower( LGS_PROP_ACTIVE_VALUES ) ) );

	return in_array( $value, $active, true );
}

/* -------------------------------------------------------------------------
 * Local state
 * ---------------------------------------------------------------------- */

/**
 * Every property post that carries a remote ID.
 *
 * @return array[] Each entry: array( 'post_id', 'remote_id', 'status', 'title', 'auto_drafted' ).
 */
function lgs_prop_local_posts() {
	$posts = get_posts( array(
		'post_type'        => LGS_PROP_POST_TYPE,
		'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'numberposts'      => -1,
		'fields'           => 'ids',
		'suppress_filters' => false,
		'meta_query'       => array(
			array(
				'key'     => LGS_PROP_REMOTE_ID_META,
				'compare' => 'EXISTS',
			),
		),
	) );

	$out = array();
	foreach ( $posts as $post_id ) {
		$remote_id = (string) get_post_meta( $post_id, LGS_PROP_REMOTE_ID_META, true );
		if ( '' === $remote_id ) {
			continue;
		}
		$out[] = array(
			'post_id'      => (int) $post_id,
			'remote_id'    => $remote_id,
			'status'       => get_post_status( $post_id ),
			'title'        => get_the_title( $post_id ),
			'auto_drafted' => (bool) get_post_meta( $post_id, LGS_PROP_DRAFTED_META, true ),
		);
	}

	return $out;
}

/* -------------------------------------------------------------------------
 * The reconciliation itself
 * ---------------------------------------------------------------------- */

/**
 * @param bool $dry_run True to report without changing anything.
 * @param bool $force   True to bypass the LGS_PROP_MIN_RATIO safety guard.
 * @return array Report.
 */
function lgs_prop_reconcile( $dry_run = true, $force = false ) {
	$report = array(
		'ran_at'    => gmdate( 'c' ),
		'dry_run'   => (bool) $dry_run,
		'aborted'   => false,
		'reason'    => '',
		'active'    => 0,
		'local'     => 0,
		'to_draft'  => array(),
		'to_publish' => array(),
	);

	$active_ids = lgs_prop_fetch_active_ids();
	if ( is_wp_error( $active_ids ) ) {
		$report['aborted'] = true;
		$report['reason']  = 'API: ' . $active_ids->get_error_message();
		lgs_prop_finish( $report );
		return $report;
	}

	$local             = lgs_prop_local_posts();
	$published_count   = count( array_filter( $local, function ( $p ) {
		return 'publish' === $p['status'];
	} ) );
	$report['active']  = count( $active_ids );
	$report['local']   = count( $local );

	if ( 0 === count( $active_ids ) ) {
		$report['aborted'] = true;
		$report['reason']  = 'API returned zero active properties — refusing to draft everything.';
		lgs_prop_finish( $report );
		return $report;
	}

	if ( ! $force && $published_count > 0
		&& count( $active_ids ) < ( LGS_PROP_MIN_RATIO * $published_count ) ) {
		$report['aborted'] = true;
		$report['reason']  = sprintf(
			'API reported %d active but %d are published locally — below the %d%% guard. Re-run with --force if this drop is real.',
			count( $active_ids ),
			$published_count,
			(int) ( LGS_PROP_MIN_RATIO * 100 )
		);
		lgs_prop_finish( $report );
		return $report;
	}

	$active_map = array_flip( $active_ids );

	foreach ( $local as $prop ) {
		$is_active = isset( $active_map[ $prop['remote_id'] ] );

		if ( ! $is_active && 'publish' === $prop['status'] ) {
			$report['to_draft'][] = $prop;
			if ( ! $dry_run ) {
				wp_update_post( array(
					'ID'          => $prop['post_id'],
					'post_status' => 'draft',
				) );
				update_post_meta( $prop['post_id'], LGS_PROP_DRAFTED_META, time() );
			}
			continue;
		}

		// Came back to life, and it was us who drafted it — restore it.
		if ( $is_active && 'draft' === $prop['status'] && $prop['auto_drafted'] ) {
			$report['to_publish'][] = $prop;
			if ( ! $dry_run ) {
				wp_update_post( array(
					'ID'          => $prop['post_id'],
					'post_status' => 'publish',
				) );
				delete_post_meta( $prop['post_id'], LGS_PROP_DRAFTED_META );
			}
		}
	}

	lgs_prop_finish( $report );
	return $report;
}

/**
 * @param array $report
 * @return void
 */
function lgs_prop_finish( array $report ) {
	if ( ! $report['dry_run'] ) {
		update_option( LGS_PROP_LAST_RUN_OPTION, $report['ran_at'], false );
	}

	$line = $report['aborted']
		? sprintf( '%s ABORTED%s: %s', $report['ran_at'], $report['dry_run'] ? ' (dry run)' : '', $report['reason'] )
		: sprintf(
			'%s %s active=%d local=%d drafted=%d restored=%d',
			$report['ran_at'],
			$report['dry_run'] ? 'DRY' : 'RUN',
			$report['active'],
			$report['local'],
			count( $report['to_draft'] ),
			count( $report['to_publish'] )
		);

	$log = get_option( LGS_PROP_LOG_OPTION, array() );
	if ( ! is_array( $log ) ) {
		$log = array();
	}
	$log[] = $line;
	update_option( LGS_PROP_LOG_OPTION, array_slice( $log, -50 ), false );
}

/**
 * @param array $report
 * @return string
 */
function lgs_prop_format_report( array $report ) {
	$out = array();
	$out[] = sprintf( 'Property reconcile — %s%s', $report['ran_at'], $report['dry_run'] ? ' (dry run, nothing changed)' : '' );

	if ( $report['aborted'] ) {
		$out[] = 'ABORTED: ' . $report['reason'];
		return implode( "\n", $out ) . "\n";
	}

	$out[] = sprintf( 'Active in booking API: %d   Property posts with a remote ID: %d', $report['active'], $report['local'] );
	$out[] = '';
	$out[] = sprintf( 'Published locally but NOT active in the API — %s to draft (%d):',
		$report['dry_run'] ? 'would set' : 'set', count( $report['to_draft'] ) );
	foreach ( $report['to_draft'] as $p ) {
		$out[] = sprintf( '  #%d  [%s]  %s', $p['post_id'], $p['remote_id'], $p['title'] );
	}
	if ( ! $report['to_draft'] ) {
		$out[] = '  (none)';
	}

	$out[] = '';
	$out[] = sprintf( 'Active again — %s to publish (%d):',
		$report['dry_run'] ? 'would restore' : 'restored', count( $report['to_publish'] ) );
	foreach ( $report['to_publish'] as $p ) {
		$out[] = sprintf( '  #%d  [%s]  %s', $p['post_id'], $p['remote_id'], $p['title'] );
	}
	if ( ! $report['to_publish'] ) {
		$out[] = '  (none)';
	}

	return implode( "\n", $out ) . "\n";
}

/* -------------------------------------------------------------------------
 * Ways to run it by hand
 * ---------------------------------------------------------------------- */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'lgs-props reconcile', function ( $args, $assoc ) {
		$dry    = isset( $assoc['dry-run'] );
		$force  = isset( $assoc['force'] );
		$report = lgs_prop_reconcile( $dry, $force );
		WP_CLI::log( lgs_prop_format_report( $report ) );
		if ( $report['aborted'] ) {
			WP_CLI::error( $report['reason'] );
		}
		WP_CLI::success( 'Done.' );
	} );
}

/**
 * Browser fallback for installs without WP-CLI. Administrators only.
 *   /?lgs_reconcile=dry                       report only
 *   /?lgs_reconcile=run&_wpnonce=...          apply (nonce required)
 */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['lgs_reconcile'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$mode = sanitize_key( wp_unslash( $_GET['lgs_reconcile'] ) );

	if ( 'run' === $mode ) {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'lgs_prop_run' ) ) {
			wp_die( 'Bad nonce. Run the dry report first — it prints a valid apply link.' );
		}
		$report = lgs_prop_reconcile( false, ! empty( $_GET['force'] ) );
	} else {
		$report = lgs_prop_reconcile( true, false );
	}

	$body = lgs_prop_format_report( $report );

	if ( 'run' !== $mode ) {
		$body .= "\nApply: " . wp_nonce_url( home_url( '/?lgs_reconcile=run' ), 'lgs_prop_run' ) . "\n";
	}

	$log = get_option( LGS_PROP_LOG_OPTION, array() );
	if ( is_array( $log ) && $log ) {
		$body .= "\nRecent runs:\n  " . implode( "\n  ", array_slice( $log, -10 ) ) . "\n";
	}

	header( 'Content-Type: text/plain; charset=utf-8' );
	echo $body; // phpcs:ignore WordPress.Security.EscapeOutput -- plain text response.
	exit;
} );
