<?php

namespace App\Http\Controllers;

use App\Ingredients;
use JWTAuth;
use Illuminate\Http\Request;

class IngredientsController extends Controller
{
    
	public function __construct(){
        $this->middleware('jwt.auth', ['except' => ['getIngredients', 'storeIngredients', 'updateIngredients']]);
    }
	
	/* 
     * Get ingredients of a specific recipe 
     */
    public function getIngredients($recipeId){
    	$ingredients = \App\Ingredients::where('recipe_id', '=', $recipeId)
    		->orderBy('ingredient_id')
            ->get(['ingredient_id', 'ingredient_name', 'quantity']) 
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
    public function updateIngredients($data){
		$updateIngredient = new Ingredients();
		$updateIngredient->exists = true;
		$updateIngredient->ingredient_id = $data['ingredientId'];
		$updateIngredient->ingredient_name = $data['ingredient'];
		$updateIngredient->quantity = $data['quantity'];
		$updateIngredient->recipe_id = $data['recipe_id'];
		$updateIngredient->save();

    }

    /*
     * Delete an ingredient of a specific recipe 
     */
    public function deleteIngredients($ingredientId){
		//$this->user = JWTAuth::parseToken()->authenticate();

		$ingredient = Ingredients::find($ingredientId);   
		
		if(!$ingredient){
    		return response()->json([
    			'success' => false,
    			'message' => 'Error, occured.'
    		], 400);
    	}
		
		$deleted = $ingredient->delete();
			
    	if($deleted){
			
    		return response()->json([
    			'success' => true
    		], 200);
    	}
    	else{
    		return response()->json([
    			'success' => false,
    			'message' => 'Ingredient could not deleted.'
    		], 500);
    	}
	
    }
}
