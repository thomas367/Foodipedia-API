<?php

namespace App\Http\Controllers;

use App\Recipes;
use JWTAuth;
use Validator, DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RecipesController extends Controller
{
    protected $user;

    public function __construct(){
        $this->middleware('jwt.auth', ['except' => ['index', 'getRecipeData', 'searchRecipes']]);
    }

    /*
     * Return all recipes.
     */
    public function index(){
    	$recipes = \App\Recipes::orderBy('created_at', 'desc')
            ->get(['recipe_id', 'recipe_name', 'cuisine', 'category', 'img_path' , 'created_at']) 
            ->toArray();

        return $recipes;
    }
	/*
	 * Return all recipes with a keyword based 
	 * on cuisine, caterogy or recipe name.
	 */
	public function searchRecipes($keyword=null){
		$recipes = \App\Recipes::where('cuisine', '=', $keyword)
			->orWhere('category', '=', $keyword)
			->orWhere('recipe_name', '=', $keyword)
			->orderBy('created_at', 'desc')
            ->get(['recipe_id', 'recipe_name', 'cuisine', 'category', 'img_path', 'created_at'])
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
            ->get(['recipe_id', 'recipe_name', 'cuisine', 'category', 'img_path' , 'created_at']) 
    		->toArray(); 
    }
	
    /*
     * Return all the data of the current recipe (with ingredients).
     */
    public function getRecipeData($recipeId=null){
        
        $recipe = \App\Recipes::where('recipe_id', '=', $recipeId)
            ->get(['recipe_id', 'recipe_name', 'cuisine', 'category', 'img_path' ,'directions', 'created_at'])
            ->toArray();

        $ingredientsControllerObject = new IngredientsController();
        $ingredients = $ingredientsControllerObject->getIngredients($recipeId);
        

		if(!$recipe){
			return response()->json([
				'error' => 'Recipe not found.'
			], 404);
		}
		
        return response()->json([
            'recipe' => $recipe,
            'ingredients' => $ingredients
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
    		'directions' => 'required',
            'image' => 'required|image|max:600'
    	]);

		if ($validator->fails()) {
            return response()->json([
				'success' => false,
				'error' => $validator->messages()
			]);
        }

        $extension=$request->file('image')->getClientOriginalExtension();        
        $imgName = date('dmYHis').uniqid().'.'.$extension;
       
        $image = $request->file('image');
        $image->storeAs('public/images',$imgName);
        				
    	$recipe = new Recipes();
    	$recipe->recipe_name = $request->get('recipe_name');
    	$recipe->cuisine = $request->get('cuisine');
    	$recipe->category = $request->get('category');
    	$recipe->directions = $request->get('directions');
        $recipe->img_path = url('/storage/images/'.$imgName);
        $recipe->created_at = date('Y-m-d H:i:s');
		$recipe->user_id = $this->user->user_id;
        $recipe->save();
        
        /* Gets ingredients data */
        $ingredients = explode(',', $request->get('ingredient'));
        $quantities = explode(',', $request->get('quantity'));
        
        /* Sets each ingredient row with quantity */
        foreach ($ingredients as $key => $value) {
            $data = array(
                'ingredient' => $value,
                'quantity' => $quantities[$key],
                'recipe_id' =>$recipe->recipe_id 
            );
            /* Call function from Ingredient controller to submit data. */
            $ingredientsControllerObject = new IngredientsController();
            $ingredientsControllerObject->storeIngredients($data);
        }      
	
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
            'directions' => 'required',
			'image' => 'image|max:600'
        ]);
		
		if ($validator->fails()) {
            return response()->json([
				'success' => false,
				'error' => $validator->messages()
			]);
        }
		
		$oldImagePath = $recipe->img_path;	
		$oldImage = pathinfo($oldImagePath);

		$updated = new Recipes();
        $updated->exists = true;
		$updated->recipe_id = $recipeId;
		$updated->recipe_name = $request->get('recipe_name');
		$updated->cuisine = $request->get('cuisine');
		$updated->category = $request->get('category');
		$updated->directions = $request->get('directions');
		$updated->created_at = date('Y-m-d H:i:s');
		$updated->user_id = $this->user->user_id;
		
		/*
		 * Check if the user uploads also a new image
		 * if yes then save the new image and after this
		 * deletes the old.
		 */
		if($request->file('image')){
			$extension=$request->file('image')->getClientOriginalExtension();        
			$imgName = date('dmYHis').uniqid().'.'.$extension;
       
			$image = $request->file('image');
			$image->storeAs('public/images',$imgName);
			$updated->img_path = url('/storage/images/'.$imgName);
			
			File::delete('storage/images/'.$oldImage['basename']);
		}
		
		$updated->save();
		
		/* Gets ingredients data */
        $ingredientsIds = explode(',', $request->get('ingredientId'));
		$ingredients = explode(',', $request->get('ingredient'));
        $quantities = explode(',', $request->get('quantity'));
        
        /* Sets each ingredient row with quantity */
        foreach ($ingredients as $key => $value) {
            $data = array(
                'ingredient' => $value,
				'ingredientId' => $ingredientsIds[$key],
                'quantity' => $quantities[$key],
                'recipe_id' =>$recipeId 
            );
            /* Call function from Ingredient controller to submit data. */
            $ingredientsControllerObject = new IngredientsController();
			if($data['ingredientId']){
				$ingredientsControllerObject->updateIngredients($data);
			}
			else{	
            	$ingredientsControllerObject->storeIngredients($data);
			}
        }
		 
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
		
		$imagePath = $recipe->img_path;	
		$image = pathinfo($imagePath);
		
		$deleted = $recipe->delete();
			
    	if($deleted){
			File::delete('storage/images/'.$image['basename']);
			
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
