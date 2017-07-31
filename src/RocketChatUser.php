<?php

namespace RocketChat;

use Httpful\Request;
use RocketChat\Client;

class User extends Client {
	public $username;
	private $password;
	public $id;
	public $nickname;
	public $email;
	public $customFields;
  
	public function __construct($username, $password, $fields = array()){
		parent::__construct();
		$this->username = $username;
		$this->password = $password;
		if( isset($fields['nickname']) ) {
			$this->nickname = $fields['nickname'];
		}
		if( isset($fields['email']) ) {
			$this->email = $fields['email'];
		}
		if( isset($fields['customFields']) ) {
			$this->customFields = $fields['customFields'];
		}
	}

	/**
	* Authenticate with the REST API.
	*/
	public function login($save_auth = true) {
		$response = Request::post( $this->api . 'login' )
			->body(array( 'user' => $this->username, 'password' => $this->password ))
			->send();

		if( $response->code == 200 && isset($response->body->status) && $response->body->status == 'success' ) {
			if( $save_auth) {
				// save auth token for future requests
				$tmp = Request::init()
					->addHeader('X-Auth-Token', $response->body->data->authToken)
					->addHeader('X-User-Id', $response->body->data->userId);
				Request::ini( $tmp );
			}
			$this->id = $response->body->data->userId;
			return true;
		} else {
			echo( $response->body->message . "\n" );
			return false;
		}
	}

	/**
	* Gets a user’s information, limited to the caller’s permissions.
	*/
	public function info() {
		$response = Request::get( $this->api . 'users.info?userId=' . $this->id )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$this->id = $response->body->user->_id;
			$this->nickname = $response->body->user->name;
			$this->email = $response->body->user->emails[0]->address;
			return $response->body;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Create a new user.
	* for customFields we have to send json-object so we use json_decode with inner json_encode
	*/
	public function create() {
		$bodydata=array(
				'name' => $this->nickname,
				'email' => $this->email,
				'username' => $this->username,
				'password' => $this->password,
		);
		if (isset($this->customFields))
		   $bodydata['customFields'] = json_decode(json_encode($this->customFields), JSON_FORCE_OBJECT);
		$response = Request::post( $this->api . 'users.create' )
			->body($bodydata)
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$this->id = $response->body->user->_id;
			return $response->body->user;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Update a user.
	* for data we have to send json-object so we use json_decode with inner json_encode
	* using the attributes of this class as update-values
	*/
	public function update() {
		$response = Request::post( $this->api . 'users.update' )
			->body(array(
				'userId' => $this->id,
				'data' => json_decode(json_encode(array('name' => $this->nickname,'email'=>$this->email,'username'=>$this->username,'password'=>$this->password), JSON_FORCE_OBJECT)),
			))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$this->id = $response->body->user->_id;
			return $response->body->user;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* Deletes an existing user.
	*/
	public function delete() {

		// get user ID if needed
		if( !isset($this->id) ){
			$this->me();
		}
		$response = Request::post( $this->api . 'users.delete' )
			->body(array('userId' => $this->id))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}
}
