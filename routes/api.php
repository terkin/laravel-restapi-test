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

Route::get('request-token', 'ApiController@requestToken');
Route::get('/version', 'VideoController@version');
Route::group(['middleware' => ['auth.device']], function () {
//	Route::get('/{action?}', function(Request $request,  $action = 'index'){
//		$apiVersion = $request->header('x-api-version');
//		$apiVersionPath = (null === $apiVersion) ? '' : "\V{$apiVersion}";
//		$className = "App\Http\Controllers" . $apiVersionPath . "\VideoController";
//		if(!is_callable([$className, $action])) {
//			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
//		}
//		$app = app();
//		return $app->call($className . '@' . $action);
//	});

	Route::get('/', 'VideoController@index');
	Route::get('status/{id}', 'VideoController@status');
	Route::get('restart/{id}', 'VideoController@restart');
	Route::post('trim', 'VideoController@trim');
});

Route::group(['prefix' => 'v2'], function () {
	Route::get('/version', 'V2\VideoController@version');
});