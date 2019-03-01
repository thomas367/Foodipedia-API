<?php

namespace App\Http\Controllers;

use App\Ingredients;
use Illuminate\Http\Request;

class IngredientsController extends Controller
{
    /* 
     * Get ingredients of a specific recipe 
     */
    public function getIngredients($recipeId){
    	$ingredients = \App\Ingredients::where('recipe_id', '=', $recipeId)
    		->orderBy('ingrentient_id')
            ->get(['ingredient_name', 'quantity']) 
            ->toArray();

        return $ingredients;
    }

    /* 
     * Store ingredients of a specific recipe 
     */
    public function storeIngredients($data){

    	$ingredient = new Ingredients();
    	$ingredient->ingredient_name = $data['ingredient'];
    	$ingredient->quantity = $data['quantity'];
    	$ingredient->recipe_id = $data['recipe_id'];
    	$ingredient->save();
    }

    /* 
     * Update ingredients of a specific recipe 
     */
    public function updateIngredients(){

    }

    /*
     * Delete an ingredient of a specific recipe 
     */
    public function deleteIngredients(){

    }
}
