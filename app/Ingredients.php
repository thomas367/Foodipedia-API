<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ingredients extends Model
{
    protected $primaryKey = 'ingredient_id';
    protected $foreignKey = 'recipe_id';

    protected $fillable = [
    	'ingrentient_name', 'quantity'
    ];

    public function recipes(){
    	return $this->belongsTo('App\Recipes', 'recipe_id', 'recipe_id');
    }
}
