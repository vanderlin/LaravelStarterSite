<?php namespace core\models;
use Zizaco\Confide\ConfideUser;
use Zizaco\Confide\ConfideUserInterface;
use Zizaco\Entrust\HasRole;
use Eloquent, DB, Auth;

class User extends Eloquent implements ConfideUserInterface {
    
    use ConfideUser;
    use HasRole; 
    protected $hidden = array('password');

    public static function findFromData($data) {
        return \User::where('id', '=', $data)->orWhere('username', '=', $data)->orWhere('email', '=', $data)->first();
    }

    public function profileImage() {
    	return $this->morphOne('Asset', 'assetable');
    }

    public function hasDefaultProfileImage() {
       return $this->profileImage()->first() == null;
    }

    public function getProfileImageAttribute() {    
        $img = $this->profileImage()->first();
        if($img == null) return Asset::where('filename', '=', 'default.png')->first();
        return $img;
    }

    public function getName() {
        return (empty($this->firstname)||empty($this->lastname)) ? $this->username : $this->firstname." ".$this->lastname;
    }

    public function getRoleName() {

        return $this->roles() ? $this->roles()->first()->name : 'no role';
    }

    public function getProfileLinkAttribute() {
        return URL::to('traveler/'.$this->username);
    }

    public function isMe() {
        return Auth::user()->id == $this->id;
    }

    static function findFromEmail($email) {

        $user = User::where('email', '=', $email)->first();
        
        return $user;

    }


}