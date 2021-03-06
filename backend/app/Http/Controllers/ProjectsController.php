<?php

namespace App\Http\Controllers;

use App\Project;
use App\Role;
use App\RoleUser;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectsController extends Controller
{
     /**
     * Obtener todos los proyectos en los que participa un docente
     *
     */
    public function getProyectos(Request $request)
    {

        $projecto = Project::withCount('user_roles')->get();
        if (empty($projecto))
        {
            $returnData = array(
                'status' => 'error',
                'message' => 'An error occurred!'
            );
            return response()->json($returnData, 500);
        }
        foreach($projecto as &$proy){
            $pm = $proy->user_roles()->where("role_id", 1)->first();
            $user = null;
            if($pm != null)
                $user = User::find($pm->user_id);
            $proy->project_manager = $user;;
        }
        return response()->json($projecto);
    }

    /**
     * Obtener todo lo necesario de un proyecto
     */
    public function getProyecto(Request $request, $id)
    {
        $projecto = Project::where('id', $id)->get();
        if (empty($projecto))
        {
            $returnData = array(
                'status' => 'error',
                'message' => 'An error occurred!'
            );
            return response()->json($returnData, 500);
        }
        return response()->json($projecto);
    }

    /**
     * Eliminar un proyecto en el que participa un docente
     */
    public function eliminarProyecto(Request $request, $id)
    {
        try {
            $projecto = Project::find($id);
            $projecto->delete();
            $returnData = array(
                'status' => 'complete',
                'message' => 'Eliminado'
            );
            return response()->json($returnData);

          } catch(\Illuminate\Database\QueryException $ex){
            dd($ex->getMessage());
            // Note any method of class PDOException can be called on $ex.
            $returnData = array(
                'status' => 'error',
                'message' => 'No se ha podido eliminar el proyecto'
            );
            return response()->json($returnData, 500);
          }
    }

    /**
     * Editar un proyecto en el que participa un docente
     */
    public function editarProyecto(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $description = $request->input('description');

        $projecto = Project::where('id', $id)->get();

        if (empty($projecto))
        {
            $returnData = array(
                'status' => 'error',
                'message' => 'An error occurred!'
            );
            return response()->json($returnData, 500);
        }
        else
        {
            $projecto->name = $name;
            $projecto->description = $description;
            $projecto->save();

            $returnData = array(
                'status' => 'complete',
                'message' => 'Editado exitosamente'
            );
            return response()->json($returnData);
        }
    }



    /**
     * Un docente crea un proyecto
     */
    public function crearProyecto(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $description = $request->input('description');

        $projecto = new Project;
        $projecto->id = $id;
        $projecto->name = $name;
        $projecto->description = $description;

        try {
            $projecto->save();
            $returnData = array(
                'status' => 'complete',
                'message' => 'Se ha creado el proyecto de forma correcta'
            );
            return response()->json($returnData);

          } catch(\Illuminate\Database\QueryException $ex){
            dd($ex->getMessage());
            // Note any method of class PDOException can be called on $ex.
            $returnData = array(
                'status' => 'error',
                'message' => 'No se ha podido crear el proyecto'
            );
            return response()->json($returnData, 500);
          }
    }


    /**
     * Agregar una lista de alumnos a un proyecto
     */
    public function agregarAlumnosAProyecto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'students' => 'required|array',
            'project_id' => 'required',
        ]);

        if ($validator->fails()) {
            return ['errors'=>$validator->errors()];
        }

        $proyecto = Project::find($request->project_id);
        $n = 0;
        foreach($request->students as $student){
            $role = Role::find($student['role_id']);
            $user = User::find($student['user_id']);
            $user->roles()->sync($role);

            $role_user = RoleUser::where('user_id', $user->id)->where('role_id', $role->id)->first();
            $proyecto->user_roles()->sync($role_user, false);
            $n++;
        }

        return response()->json(["status" => "complete", "enrolled" => $n]);
    }

    public function getAlumnosProyecto(Request $request, $id) {
        $proyecto = Project::find($id);
        $user_roles = $proyecto->user_roles;
        $data = [];
        foreach($user_roles as $usr){
            $userr = RoleUser::find($usr->id);
            $selected_user = $userr->user;
            $selected_user['project_role'] = $usr->role->name;
            array_push($data, $selected_user);
        }
        return $data;
    }

    /**
     * Elimina un alumno de un proyecto
     */
    public function eliminarAlumnoDeUnProyecto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'project_id' => 'required',
        ]);

        if ($validator->fails()) {
            return ['errors'=>$validator->errors()];
        }

        $proyecto = Project::find($request->project_id);
        $user = User::find($request->user_id);
        $buscado =  $proyecto->user_roles()->where('user_id', $user->id)->first();
        return ($proyecto->user_roles()->detach($buscado))?response()->json(['status' => 'ok']):response()->json(['status' => 'failed']);

    }

    public function getRoles(Request $request){
        return Role::all();
    }

        /**
     * Un docente crea un proyecto
     */
    public function createAndAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'year' => 'required',
            'semester' => 'required',
            'description' => 'required',
            'students' => 'required|array',
        ]);

        if ($validator->fails()) {
            return ['errors'=>$validator->errors()];
        }

        $projecto = new Project();
        $projecto->name = $request->name;
        $projecto->description = $request->description;
        $projecto->year = $request->year;
        $projecto->semester = $request->semester;

        try {
            $projecto->save();
            $n = 0;
            foreach($request->students as $student){
                $role = Role::find($student['role_id']);
                $user = User::find($student['user_id']);
                $user->roles()->sync($role);

                $role_user = RoleUser::where('user_id', $user->id)->where('role_id', $role->id)->first();
                $projecto->user_roles()->sync($role_user, false);
                $n++;
            }
        }catch(Exception $ex){
            return response()->json([
                'status' => false,
                'msg' => 'El proyecto no se pudo crear.'
            ]);
        }

        return response()->json(["status" => true, "enrolled" => $n, "msg" => "Proyecto creado correctamente."]);
    }

    public function getJefeProyecto(Request $request, $id){
        $proyecto = Project::find($id);
        $user_roles = $proyecto->user_roles()->where("role_id", 1)->first();
        if($user_roles==null){
            return response()->json(["success" => false]);
        }
        $user = User::find($user_roles->user_id);
        $user->success = true;
        return $user;
    }
}
