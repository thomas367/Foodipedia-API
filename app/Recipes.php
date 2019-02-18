<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recipes extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'recipe_id';
    protected $foreignKey = 'user_id';

    protected $fillable = [
    	'recipe_name', 'cuisine', 'category', 'directions'
    ];

    public function ingredients(){
        return $this->hasMany('App\Ingredients', 'recipe_id', 'recipe_id');
    }

    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'user_id');
    }
}
