<?php

namespace RocketChat;

use Httpful\Request;
use RocketChat\Client;

/**
* Manage a settings collection.
* The collection can be read/saved from/into a json file.
*/
class Settings extends Client {

	private $file;

	/**
	* $file : path to the json file containing the setting collection.
	*/
	public function __construct($file){
		parent::__construct();
		$this->file = $file;
	}

	/**
	* Gets a setting from its ID.
	*/
	public function get( $id ){
		$response = Request::get( $this->api . 'settings/' . $id )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return $response->body->value;
		} else {
			echo( $response->body->error . "\n" );
		}
	}

	/**
	* Updates a setting.
	*/
	public function update( $id, $value ){
		$response = Request::post( $this->api . 'settings/' . $id )
			->body(array("value" => $value))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
		}
	}

	/**
	* Load the local setting file content and check if its value is the same
	* as online. Print the difference if it finds one.
	*/
	public function check(){
		$f = fopen($this->file, 'r');
		$settings = json_decode( fread($f, filesize($this->file)) );
		fclose($f);

		if( $settings === null ) {
			echo "Erreur de décodage json pour le fichier {$this->file}\n";
			return;
		}

		foreach($settings as $id => $value){
			$check_val = $this->get($id);

			if(is_object($value) && is_object($check_val) && $value == $check_val) {
				// object comparison
				continue;
			} else if( $value !== $check_val ) {
				if($check_val === true) $check_val = 'true';
				if($check_val === false) $check_val = 'false';
				if($check_val === "") $check_val = '""';
				if(is_object($check_val)) $check_val = print_r($check_val, true);
				if($value === true) $value = 'true';
				if($value === false) $value = 'false';
				if($value === "") $value = '""';
				if(is_object($value)) $value = print_r($value, true);

				echo "$id : $check_val instead of $value\n";
			}
		}

		// make method chainable
		return $this;
	}

	/**
	* Write the remote settings to the local file.
	*/
	public function saveToFile(){
		$f = fopen($this->file, 'r');
		$settings = json_decode( fread($f, filesize($this->file)) );
		fclose($f);
		foreach($settings as $id => $value){
			$settings->{$id} = $this->get($id);
		}
		$f = fopen($this->file, 'w');
		fwrite($f, json_encode($settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
		fclose($f);

		// make method chainable
		return $this;
	}

	/**
	* Update the remote settings according to data in the local file
	*/
	public function updateRemote(){
		$f = fopen($this->file, 'r');
		$settings = json_decode( fread($f, filesize($this->file)) );
		fclose($f);
		foreach($settings as $id => $value){
			$this->update($id, $value);
		}

		// make method chainable
		return $this;
	}
}

