<?php namespace core\models;

use Eloquent;
use File;
use URL;

class Asset extends \Eloquent {
	protected $fillable = [];


    // ------------------------------------------------------------------------
    public function delete() {
      parent::delete();
      if(File::exists($this->relativeURL())) {
        File::delete($this->relativeURL());
      }
    }

    // ------------------------------------------------------------------------
    public function __construct($attributes = array(), $exists = false) {
      parent::__construct($attributes, $exists);

      $this->uid = uniqid();
  
    }

    // ------------------------------------------------------------------------
    public function generateUID() {
      $this->uid = uniqid();
    }

    // ------------------------------------------------------------------------
    static function imageSizes() {
      return array(

        );
    }

	  // ------------------------------------------------------------------------
  	public function assetable() {
        return $this->morphTo();
    }

    // ------------------------------------------------------------------------
    public function getName() {
      if($this->name == null) return $this->filename;
      return $this->name;
    }

  	// ------------------------------------------------------------------------
  	public function saveRemoteImage($url, $save_path, $filename) {

  		$this->filename = $filename;
		  $this->path = $save_path; 

  		if(!File::exists($save_path)) {
  			File::makeDirectory($save_path, 0755, true);			
  		}
      file_put_contents($save_path.'/'.$filename, file_get_contents($url));
  	}

    // ------------------------------------------------------------------------
    public function resizeImageURL($options=array()) {
      return URL::to('images/'.$this->id.(is_string($options)?'/'.$options:''));  
    }

    // ------------------------------------------------------------------------
    public function getUrlAttribute() {
      return URL::to($this->path.'/'.$this->filename);
    }

	  // ------------------------------------------------------------------------
  	public function url($options=array()) {
      return URL::to('images/'.$this->id.(is_string($options)?'/'.$options:''));  
  	}

    // ------------------------------------------------------------------------
    public function relativeURL($options=array()) {
      return $this->path.'/'.$this->filename;
    }




}