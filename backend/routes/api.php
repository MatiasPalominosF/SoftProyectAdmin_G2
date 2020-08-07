<?php

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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('logout', 'AuthController@logout');
    Route::post('updateinfo', 'AuthController@updateInfo');

    Route::group(['middleware' => 'role:admin'], function () {
        Route::get('users', 'UsersController@getUsuarios');
        Route::get('students', 'UsersController@getEstudiantes');
        Route::get('teachers', 'UsersController@getProfesores');
        Route::get('users/{id}', 'UsersController@getUser');
        Route::post('users', 'UsersController@crearUsuario');
        Route::put('users/{id}', 'UsersController@editarUsuario');
        Route::delete('users/{id}', 'UsersController@eliminarUsuario');
        Route::post('users/uploadfile', 'UsersController@uploadFile');
    });

    Route::get('roles', 'ProjectsController@getRoles');
    Route::group(['middleware' => 'role:admin,teacher'], function () {
        Route::get('courses', 'CoursesController@getCursos');
        Route::get('courses/{id}', 'CoursesController@getCurso');
        Route::post('courses', 'CoursesController@crearCurso');
        Route::put('courses/{id}', 'CoursesController@editarCurso');
        Route::delete('courses/{id}', 'CoursesController@eliminarCurso');
        Route::post('courses/assign', 'CoursesController@asignarProfesorACurso');
        Route::post('courses/createmass', 'CoursesController@createAndAdd');
        Route::get('courses/list/{id}', 'CoursesController@getUserList');
        Route::post('courses/uploadfile/{id}', 'CoursesController@uploadFile');
    });
    Route::get('projects', 'ProjectsController@getProyectos');
    Route::get('projects/{id}', 'ProjectsController@getProyecto');
    Route::post('projects', 'ProjectsController@crearProyecto');
    Route::put('projects/{id}', 'ProjectsController@editarProyecto');
    Route::delete('projects/{id}', 'ProjectsController@eliminarProyecto');
    Route::post('projects/assign', 'ProjectsController@agregarAlumnosAProyecto');
    Route::get('projects/list/{id}', 'ProjectsController@getAlumnosProyecto');
    Route::delete('projects/detach', 'ProjectsController@eliminarAlumnoDeUnProyecto');
    Route::post('projects/createmass', 'ProjectsController@createAndAdd');
    Route::get('projects/getpm/{id}', 'ProjectsController@getJefeProyecto');


    Route::apiResource('userrequirements', 'UserRequirementController');
    Route::get('userrequirements/nextid/{id}', 'UserRequirementController@getNextId');
    Route::apiResource('softwarerequirements', 'SoftwareRequirementController');
    Route::get('softwarerequirements/nextid/{id}', 'SoftwareRequirementController@getNextId');
    Route::apiResource('increments', 'IncrementsController');
    Route::apiResource('testcases', 'TestCaseController');
    Route::get('testcases/nextid/{id}', 'TestCaseController@getNextId');

    Route::get('info', function(){

        $results = DB::select( DB::raw("SELECT COUNT(profile) as count, profile FROM users WHERE deleted_at IS NULL GROUP BY profile;") );

        $cursos_activos = DB::select( DB::raw("SELECT count(*) as activos FROM courses WHERE deleted_at IS NULL;") );
        $cursos_inactivos = DB::select( DB::raw("SELECT count(*) as inactivos FROM courses WHERE deleted_at IS NOT NULL;") );

        $proyectos_activos = DB::select( DB::raw("SELECT count(*) as activos FROM projects WHERE deleted_at IS NULL;") );
        $proyectos_inactivos = DB::select( DB::raw("SELECT count(*) as inactivos FROM projects WHERE deleted_at IS NOT NULL;") );

        return response()->json([
            "usuarios" => $results,
            "cursos" => [
                $cursos_activos[0],
                $cursos_inactivos[0]
            ],
            "proyectos" => [
                $proyectos_activos[0],
                $proyectos_inactivos[0]
            ],
        ]);
    });


    Route::get('user', function(Request $request){
        return Auth::user();
    });

});

Route::get('testid', function(){
    return App\UserRequirement::where('project_id', 1)->orderBy('internalId', 'desc')->first()->internalId+1;
    return App\SoftwareRequirement::where('project_id', 1)->orderBy('internalId', 'desc')->first()->internalId + 1;

});
