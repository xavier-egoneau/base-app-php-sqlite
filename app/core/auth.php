<?php 


class Auth extends Socle  {

		 public function __construct() {}

			   
		public function check() {

			if (
				singleton("session")->hasExpired() || Session::get('connect') === NULL
			):
				$this->delete();
				return false;	
			
			else: 
			
				singleton("session")->regenerate(); 
				return true;
				
			endif;
			
			
		}	 

		public function connect($log,$pass) {

			$log = trim($log);
			$pass = haash(trim($pass));
			$ip = $_SERVER["REMOTE_ADDR"];

			if(!util::validate_email( $log )):
				return false;
			endif;
			

			if(!$this->check()):
				$user =  User::where(["email"=>$log,"password"=>$pass])->get()->first();
				
				if ($user!==null) {

					Session::put('connect', true);
					Session::put('id', $user->id);
					Session::put('client_ip', $ip);

					return true;
				}else{
					return false;
				}
			else:
				return true;
			endif;	
			
		}
		
		
		public function delete() {
			

			Session::forget('connect');
			Session::forget('id');
			Session::forget('client_ip');
		} 
		
		public function get(){
		
			if($this->check()):
				$userID = Session::get("id");
				$user = User::find(['id' => $userID])->first()->toArray();
				return $user;
			else:
				return [];
			endif;
		} 
			
			



	
}

