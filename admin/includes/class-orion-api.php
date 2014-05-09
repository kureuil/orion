<?php
/**
 * Orion.
 *
 * @package   Orion_Admin
 * @author    seishynon <sshnn@outlook.com>
 * @license   GPL-2.0+
 * @link      http://sshnn.tumblr.com/
 * @copyright 2014 seishynon
 */
/**
 * API class. Used to define AJAX endpoints of the plugin.
 *
 * @package Orion_API
 * @author seishynon <sshnn@outlook.com>
 */
class Orion_API {

	/**
	 * The current JSONAPI instance
	 * @var JSONAPI
	 */
	private $api;

	/**
	 * API endpoints
	 * @var array
	 */
	private $endpoints;

	/**
	 * Response from the JSONAPI request
	 * @var array
	 */
	private $request;

	/**
	 * Returned response
	 * @var array
	 */
	private $response;

	public function __construct() {
		$this->api = new JSONAPI(
			get_option( 'orion_server_ip' ),
			get_option( 'orion_server_port' ),
			get_option( 'orion_server_username' ),
			get_option( 'orion_server_password' )
		);

		$this->endpoints = array(
			'get_server',
			'get_player',
			'get_whitelisted_players',
			'update_player_gamemode',
			'toggle_player_it',
			'send_message',
			'kick_player'
		);

		foreach ($this->endpoints as $e) {
			add_action( 'wp_ajax_orion_' . $e, array( $this, $e ) );
		}

		$this->request = array();
		$this->response = array();
	}

	private function call($method, $args = array()) {
		if ( is_array($method) && count($method) > 1 && count($args) === 0 ) {
			foreach ($method as $m) {
				$args[] = array();
			}
		}
		return $this->api->call($method, $args);
	}
	
	public function get_server() {
		$methods = array(
			'server',
			'server.performance.memory.total',
			'server.performance.memory.used',
			'players.online.names',
			'players.online.limit',
			'worlds.names',
			'plugins'
		);
		$this->request = $this->call($methods);
		foreach ($this->request as $query) {
			$this->request[$query['source']] = $query[$query['result']];
		}
		$this->response['name']                 = $this->request['server']['serverName'];
		$this->response['version']              = $this->request['server']['version'];
		$this->response['plugins_count']        = count($this->request['plugins']);
		$this->response['worlds_names']         = $this->request['worlds.names'];
		$this->response['memory_used']          = (int) $this->request['server.performance.memory.used'];
		$this->response['memory_total']         = (int) $this->request['server.performance.memory.total'];
		$this->response['memory_free']          = $this->response['memory_total'] - $this->response['memory_used'];
		$this->response['players_online_names'] = $this->request['players.online.names'];
		$this->response['players_online_count'] = count($this->request['players.online.names']);
		$this->response['players_online_limit'] = $this->request['players.online.limit'];
		wp_send_json( $this->response );
	}

	public function get_player() {
		$player_name = sanitize_text_field( $_POST['player_name'] );
		$methods = array(
			'players.name',
			'players.name.bank.has_account',
			'players.name.bank.balance'
		);
		$args = array(
			array( $player_name ),
			array( $player_name ),
			array( $player_name )
		);
		$this->request = $this->call($methods, $args);
		foreach ($this->request as $query) {
			$this->request[$query['source']] = $query[$query['result']];
		}
		$this->response = $this->request['players.name'];
		$this->response['has_account'] = $this->request['players.name.bank.has_account'];
		$this->response['balance'] = $this->request['players.name.bank.balance'];
		wp_send_json( $this->response );
	}

	public function get_whitelisted_players() {
		$this->response = $this->call( 'players.whitelisted.names' );
		wp_send_json( $this->response[0][$this->response[0]['result']] );
	}

	public function update_player_gamemode() {
		$player_name = sanitize_text_field( $_POST['player_name'] );
		$gamemode_id = intval( $_POST['gamemode_id'] );
		$this->request = $this->call( 'players.name.set_game_mode', array( $player_name, $gamemode_id ) );
		if ( $this->request[0]['is_success'] ) {
			wp_send_json( array( 'is_success' => true ) );
		} else {
			wp_send_json( array( 'is_success' => false ) );
		}
	}

	public function toggle_player_it() {
		$player_name = sanitize_text_field( $_POST['player_name'] );
		$toggle      = sanitize_text_field( $_POST['toggle'] );

		$is_it = ( $_POST['is_it'] === 'true' );
		$is_it = is_bool($is_it) ? $is_it : false;

		$method = '';
		$args   = array( $player_name );

		switch ($toggle) {
			case 'ban':
				$method = ( $is_it ) ? 'pardon' : 'ban';
				if ( ! $is_it )
					$args[] = __( 'You\'ve been banned.', 'orion' );
				break;
			case 'op':
				$method = ( $is_it ) ? 'deop' : 'op';
				break;
			case 'whitelist':
				$method = ( $is_it ) ? 'unwhitelist' : 'whitelist';
				break;
			default:
				wp_send_json_error();
				break;
		}

		$this->request = $this->call( 'players.name.' . $method, $args );

		( !$this->request[0]['is_success'] ) ? wp_send_json_success() : wp_send_json_error(['player' => $player_name]);
	}

	public function send_message() {
		$player_name = sanitize_text_field( $_POST['player_name'] );
		$message = sanitize_text_field( $_POST['message'] );
		$this->call( 'players.name.send_message', array( $player_name, $message ) );
	}

	public function kick_player() {
		$player_name = sanitize_text_field( $_POST['player_name'] );
		$this->call( 'players.name.kick', array( $player_name, __( 'You\'ve been kicked.', 'orion' ) ) );
	}
}