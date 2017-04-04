<?php

namespace RocketChat;

use Httpful\Request;
use RocketChat\Client;

class Group extends Client {

	public $id;
	public $name;
	public $members = array();

	public function __construct($name, $members = array()){
		parent::__construct();
		if( is_string($name) ) {
			$this->name = $name;
		} else if( isset($name->_id) ) {
			$this->name = $name->name;
			$this->id = $name->_id;
		}
		foreach($members as $member){
			if( is_a($member, '\RocketChat\User') ) {
				$this->members[] = $member;
			} else if( is_string($member) ) {
				// TODO
				$this->members[] = new User($member);
			}
		}
	}

	/**
	* Creates a new private group.
	*/
	public function create(){
		// get user ids for members
		$members_id = array();
		foreach($this->members as $member) {
			if( is_string($member) ) {
				$members_id[] = $member;
			} else if( isset($member->username) && is_string($member->username) ) {
				$members_id[] = $member->username;
			}
		}

		$response = Request::post( $this->api . 'groups.create' )
			->body(array('name' => $this->name, 'members' => $members_id))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$this->id = $response->body->group->_id;
			return $response->body->group;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Retrieves the information about the private group, only if you’re part of the group.
	*/
	public function info() {
		$response = Request::get( $this->api . 'groups.info?roomId=' . $this->id )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$this->id = $response->body->group->_id;
			return $response->body;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Post a message in this group, as the logged-in user
	*/
	public function postMessage( $text ) {
		$message = is_string($text) ? array( 'text' => $text ) : $text;
		if( !isset($message['attachments']) ){
			$message['attachments'] = array();
		}

		$response = Request::post( $this->api . 'chat.postMessage' )
			->body( array_merge(array('channel' => '#'.$this->name), $message) )
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			if( isset($response->body->error) )	echo( $response->body->error . "\n" );
			else if( isset($response->body->message) )	echo( $response->body->message . "\n" );
			return false;
		}
	}

	/**
	* Removes the private group from the user’s list of groups, only if you’re part of the group.
	*/
	public function close(){
		$response = Request::post( $this->api . 'groups.close' )
			->body(array('roomId' => $this->id))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Deletes the private group.
	*/
	public function delete(){
		$response = Request::post( $this->api . 'groups.delete' )
			->body(array('roomId' => $this->id))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Removes a user from the private group.
	*/
	public function kick( $user ){
		// get group and user ids
		$userId = is_string($user) ? $user : $user->id;

		$response = Request::post( $this->api . 'groups.kick' )
			->body(array('roomId' => $this->id, 'userId' => $userId))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	 * Adds user to the private group.
	 */
	public function invite( $user ) {

		$userId = is_string($user) ? $user : $user->id;

		$response = Request::post( $this->api . 'groups.invite' )
			->body(array('roomId' => $this->id, 'userId' => $userId))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

        /**
	 * Adds owner to the private group.
	 */
	public function addOwner( $user ) {

		$userId = is_string($user) ? $user : $user->id;

		$response = Request::post( $this->api . 'groups.addOwner' )
			->body(array('roomId' => $this->id, 'userId' => $userId))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	 * Removes owner of the private group.
	 */
	public function removeOwner( $user ) {

		$userId = is_string($user) ? $user : $user->id;

		$response = Request::post( $this->api . 'groups.removeOwner' )
			->body(array('roomId' => $this->id, 'userId' => $userId))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}
}

