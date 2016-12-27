<?php

namespace RocketChat;
require_once 'lib/httpful.phar';
use Httpful\Request;

class Client{

	private $rc_instance;
	private $api_root = '/api/v1/';
	private $api;

	function __construct($url){
		$this->rc_instance = $url;
		$this->api = $this->rc_instance . $this->api_root;

		// set template request to send and expect JSON
		$tmp = Request::init()
			->sendsJson()
			->expectsJson();
		Request::ini( $tmp );
	}

	/**
	* Get version information. This simple method requires no authentication.
	*/
	public function version() {
		$response = \Httpful\Request::get( $this->api . 'info' )->send();
		return $response->body->info->version;
	}

	/**
	* Authenticate with the REST API.
	*/
	public function login( $username, $password ) {
		$response = Request::post( $this->api . 'login' )
			->body(array( 'user' => $username, 'password' => $password ))
			->send();

		if( $response->body->status == 'success' ){
			// save auth token for future requests
			$tmp = Request::init()
				->addHeader('X-Auth-Token', $response->body->data->authToken)
				->addHeader('X-User-Id', $response->body->data->userId);
			Request::ini( $tmp );
			return true;
		}
	}

	/**
	* Quick information about the authenticated user.
	*/
	public function me() {
		$response = Request::get( $this->api . 'me' )->send();

		if( $response->body->status != 'error' ) { 
			if( isset($response->body->success) && $response->body->success == true ) {
				return $response->body;
			}
		} else {
			echo( $response->body->message . "\n" );
			return false;
		}
	}
	
	/**
	* List all of the users and their information.
	*
	* Gets all of the users in the system and their information, the result is
	* only limited to what the callee has access to view.
	*/
	public function list_users(){
		$response = Request::get( $this->api . 'users.list' )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return $response->body->users;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Create a new user.
	*/
	public function create_user( $nickname, $email, $username, $password, $customFields = array() ) {
		$response = Request::post( $this->api . 'users.create' )
			->body(array( 
				'name' => $nickname,
				'email' => $email,
				'username' => $username,
				'password' => $password,
			))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return $response->body->user;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Deletes an existing user.
	*/
	public function delete_user( $userId ) {
		$response = Request::post( $this->api . 'users.delete' )
			->body(array('userId' => $userId))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Creates a new private group.
	*/
	public function create_group( $name, $members = array() ){
		// get user ids for members
		$members_id = array();
		foreach($members as $member) {
			if( is_string($member) ) {
				$members_id[] = $member;
			} else if( isset($member->username) && is_string($member->username) ) {
				$members_id[] = $member->username;
			}
		}

		$response = Request::post( $this->api . 'groups.create' )
			->body(array('name' => $name, 'members' => $members_id))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return $response->body->group;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* List the private groups the caller is part of.
	*/
	public function list_groups() {
		$response = Request::get( $this->api . 'groups.list' )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return $response->body->groups;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Removes a user from the private group.
	*/
	public function kick_from_group( $group, $user ){
		// get group and user ids
		$roomId = is_string($group) ? $group : $group->_id;
		$userId = is_string($user) ? $user : $user->_id;

		$response = Request::post( $this->api . 'groups.kick' )
			->body(array('roomId' => $roomId, 'userId' => $userId))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			var_dump($response);
			return $response->body->group;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Removes the private group from the user’s list of groups, only if you’re part of the group.
	*/
	public function close_group( $group ){
		// get group id
		$roomId = is_string($group) ? $group : $group->_id;
		$response = Request::post( $this->api . 'groups.close' )
			->body(array('roomId' => $roomId))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Post a new chat message
	*/
	public function post_message( $room, $text ){
		$roomName = is_string($room) ? $room : $room->name;
		if( $roomName[0] != '#' ) $roomName = '#'.$roomName;
		$response = Request::post( $this->api . 'chat.postMessage' )
			->body(array('channel' => $roomName, 'text' => $text))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			if( isset($response->body->error) )	echo( $response->body->error . "\n" );
			else if( isset($response->body->message) )	echo( $response->body->message . "\n" );
			return false;
		}
	}

}
