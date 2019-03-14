<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::post('login', 'ApiController@login');
Route::post('register', 'ApiController@register');
Route::get('recipes', 'RecipesController@index');
Route::get('showRecipe/{recipeId?}', 'RecipesController@showRecipeData');

Route::group(['middleware' => 'auth.jwt'], function () {		
    //Route::get('logout', 'ApiController@logout');
    //Route::get('user', 'ApiController@getAuthUser');
 
    Route::get('myRecipes', 'RecipesController@getMyRecipes');
    Route::post('storeRecipe', 'RecipesController@storeRecipe');
    Route::post('updateRecipes/{recipeId?}', 'RecipesController@updateRecipe');
    Route::post('deleteRecipes/{recipeId?}', 'RecipesController@deleteRecipe');	

});