<?php if ( ! defined("in_app")) add_error("Accès Interdit", __FILE__ , __LINE__ );

class User extends Illuminate\Database\Eloquent\Model {
     public $timestamps = true;
}