<?php if ( ! defined("in_app")) add_error("Accès Interdit", __FILE__ , __LINE__ );

class Article extends Illuminate\Database\Eloquent\Model {
    public $timestamps = true;
	
	public function scopeSearchByKeyword($query, $keyword)
	    {
	        if ($keyword!='') {
	            $query->where(function ($query) use ($keyword) {
	                $query->where("titre", "LIKE","%$keyword%")
	                    ->orWhere("tags", "LIKE", "%$keyword%")
	                    ->orWhere("url", "LIKE", "%$keyword%");
	            });
	        }
	        return $query;
	    }    
}
