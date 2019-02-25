<?php

namespace App\Http\Controllers;

use App\Recipes;
use JWTAuth;
use Validator, DB;
use Illuminate\Http\Request;

class RecipesController extends Controller
{
    protected $user;

    public function __construct(){
        $this->middleware('jwt.auth', ['except' => ['index', 'showRecipeData']]);
    }

    /*
     * Return all recipes.
     */
    public function index(){
    	$recipes = \App\Recipes::orderBy('created_at', 'desc')
            ->get(['recipe_id', 'recipe_name', 'cuisine', 'category', 'directions', 'created_at']) 
            ->toArray();

        return $recipes;
    }

    /*
     * Return all recipes of loggenIn user.
     */
    public function getMyRecipes(){
    	$this->user = JWTAuth::parseToken()->authenticate();

        return $this->user->recipes()
    		->orderBy('created_at', 'desc')
            ->get(['recipe_id', 'recipe_name', 'cuisine', 'category', 'directions', 'created_at']) 
    		->toArray(); 
    }
	
    /*
     * Return all the data of the current recipe (with ingredients).
     */
    public function showRecipeData($recipeId=null){
        
        $recipe = \App\Recipes::where('recipe_id', '=', $recipeId)
            ->get(['recipe_id', 'recipe_name', 'cuisine', 'category', 'directions', 'created_at'])
            ->toArray();

        //TODO: Call ingredients
            
        return response()->json([
            'recipe' => $recipe
            //'ingredients' => $ingredients
        ]);
    }

    /*
     * Store a recipe.
     */
    public function storeRecipe(Request $request){
        $this->user = JWTAuth::parseToken()->authenticate();

    	$validator = Validator::make($request->all(), [
    		'recipe_name' => 'required',
    		'cuisine' => 'required',
    		'category' => 'required',
    		'directions' => 'required'
    	]);

		if ($validator->fails()) {
            return response()->json($validator->messages());
        }
				
    	$recipe = new Recipes();
    	$recipe->recipe_name = $request->get('recipe_name');
    	$recipe->cuisine = $request->get('cuisine');
    	$recipe->category = $request->get('category');
    	$recipe->directions = $request->get('directions');
		$recipe->created_at = date('Y-m-d H:i:s');
		$recipe->user_id = $this->user->user_id;
        $recipe->save();
    	
        //TODO: add ingredient.

    	if($recipe){
    	    return response()->json([
    	    	'success' => true,
    	    	'recipe' => $recipe
    	    ], 200);
    	}
    	else{
    		return response()->json([
    			'success' => false,
    			'message' => 'Sorry, recipe could not be added.'
    		], 500);
    	}
    }

    /*
     * Update a recipe.
     */
    public function updateRecipe($recipeId=null, Request $request){
        $this->user = JWTAuth::parseToken()->authenticate();
    
        $recipe = $this->user->recipes()->find($recipeId);

        if(!$recipe){
            return response()->json([
                'success' => false,
                'message' => 'Error, occured.'
            ], 400);
        }

        $validator = Validator::make($request->input(), [
            'recipe_name' => 'required',
            'cuisine' => 'required',
            'category' => 'required',
            'directions' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages());
        }

        
        $updated = $recipe->fill($request->input())->save();
        
        //TODO: update ingredients.

    	if($updated){
    		return response()->json([
    			'success' => true,
                'recipe' => $recipe
    		], 200);
    	}
    	else{
    		return response()->json([
    			'success' => false,
            	'message' => 'Sorry, recipe could not be updated'
    		], 500);
    	}
    }

    /*
     * Delete a recipe.
     */
    public function deleteRecipe($recipeId){
        $this->user = JWTAuth::parseToken()->authenticate();

    	$recipe = $this->user->recipes()->find($recipeId);

    	if(!$recipe){
    		return response()->json([
    			'success' => false,
    			'message' => 'Error, occured.'
    		], 400);
    	}

    	if($recipe->delete()){
    		return response()->json([
    			'success' => true
    		], 200);
    	}
    	else{
    		return response()->json([
    			'success' => false,
    			'message' => 'Recipe could not deleted.'
    		], 500);
    	}
    }
}
