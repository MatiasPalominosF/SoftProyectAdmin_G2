<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function crearUsuario(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'enrollment' => 'required',
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'surname' => 'required',
            'password' => 'required',
            'profile' => 'required',
        ]);

        if ($validator->fails()) {
            return ['errors'=>$validator->errors()];
        }

        $rut = $request->input('rut');
        $enrollment = $request->input('enrollment');
        $email = $request->input('email');
        $name = $request->input('name');
        $surname = $request->input('surname');
        $password = $request->input('password');
        $profile = $request->input('profile');

        $user = User::create(
        [
        'rut' => $rut,
        'enrollment' => $enrollment,
        'email' => $email,
        'name' => $name,
        'surname' => $surname,
        'password' => bcrypt($password),
        'profile' => $profile,
        ]);

        $user->save();

        return response()->json([
            'status' => 200,
            'msg' => 'Usuario creado correctamente.'
        ]);
    }

    public function editarUsuario($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'enrollment' => 'required',
            'email' => 'required|email',
            'name' => 'required',
            'surname' => 'required',
            'password' => 'required',
            'profile' => 'required',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $rut = $request->input('rut');
        $enrollment = $request->input('enrollment');
        $email = $request->input('email');
        $name = $request->input('name');
        $surname = $request->input('surname');
        $password = $request->input('password');
        $profile = $request->input('profile');


        $user = User::find($id);

        $user->rut=$rut;
        $user->enrollment = $enrollment;
        $user->email = $email;
        $user->name=$name;
        $user->surname = $surname;
        $user->password = bcrypt($password);
        $user->profile = $profile;

        $user->save();

        return response()->json([
            'status' => 200,
            'msg' => 'Usuario modificado correctamente.'
        ]);
    }

    public function eliminarUsuario($idEstudiante)
    {
        $user = User::find($idEstudiante);
        $user->delete();
        return response()->json([
            'status' => 200,
            'msg' => 'Usuario eliminado correctamente.'
        ]);
    }

    /**
     * Funcion que retorna todos los estudiantes del sistema en formato json
     */
    public function getEstudiantes()
    {
        $array = User::where('profile', '=', 'student')->get();
        if(empty($array))
        {
            return response()->json([
                'status' => 500,
                'msg' => 'No existen estudiantes'
            ]);
        }
        return response()->json($array);
    }

    /**
     * Funcion que retorna todos los profesores del sistema en formato json
     */
    public function getProfesores()
    {
        $array = User::where('profile', '=', 'teacher')->get();
        if(empty($array))
        {
            return response()->json([
                'status' => 500,
                'msg' => 'No existen profesores'
            ]);
        }
        return response()->json($array);
    }

    /**
     * Funcion que retorna todos los usuarios del sistema (Excepto la informacion del admin)
     */
    public function getUsuarios()
    {
        $array = User::all();
        if(empty($array))
        {
            return response()->json([
                'status' => 500,
                'msg' => 'No hay existencias profesores/estudiantes'
            ]);
        }
        return response()->json($array);

    }

    /**
     * Retorna la informacion de un usuario en especifico.
     */
    public function getUser(Request $request, $id)
    {
        $array = User::find($id);
        if(empty($array))
        {
            return response()->json([
                'status' => 500,
                'msg' => 'Usuario no encontrado'
            ]);
        }
        return response()->json($array);
    }

    function uploadFile(Request $request){
        $this->validate($request, [
        'file'  => 'required|mimes:xls,xlsx'
        ]);

        try{

            $path = $request->file('file')->getRealPath();

            $spreadsheet = IOFactory::load($path);

            $cellNombre = $email = $spreadsheet->getActiveSheet()->getCell('A1')->getValue();
            $cellApellido = $email = $spreadsheet->getActiveSheet()->getCell('B1')->getValue();
            $cellEmail = $email = $spreadsheet->getActiveSheet()->getCell('C1')->getValue();
            if($cellNombre!='Nombre'&&$cellApellido!='Apellido(s)'&&$cellEmail!='Dirección de correo')
                return response()->json([
                    'success' => false,
                    'msg' => 'El formato del archivo es incorrecto.'
                ]);
            $lectura = true;
            $contador = 2;
            $cargados = 0;
            $errores = [
                'inexistentes' => [],
                'existentes' => []
            ];

            while($lectura){
                $nombre = $spreadsheet->getActiveSheet()->getCell('A'.$contador)->getValue();
                $apellido = $spreadsheet->getActiveSheet()->getCell('B'.$contador)->getValue();
                $email = $spreadsheet->getActiveSheet()->getCell('C'.$contador)->getValue();
                if($email==''||$email==null){
                    $lectura = false;
                    break;
                }
                $user = User::where('email', $email)->first();
                if($user==null){
                    $errores['inexistentes'][] = (string)$email;
                    $usuario = new User();
                    $usuario->name = $nombre;
                    $usuario->surname = $apellido;
                    $usuario->email = (string)$email;
                    $password = Str::random(8);
                    $usuario->password = bcrypt($password);
                    $usuario->rut = "1-9";
                    $usuario->profile = "student";
                    $usuario->save();

                    \App\Jobs\InvitarUsuario::dispatch((string)$email, ($nombre.' '.$apellido), $password)->onQueue('invitaciones');

                }else{
                    $exists = User::where('email', (string)$email)->count();
                    if($exists>0){
                        $errores['existentes'][] = (string)$email;
                    }else{
                        $cargados++;
                    }
                }
                $contador++;
            }

            return response()->json([
                'success' => true,
                'msg' => "Carga masiva de usuarios finalizada.",
                'stats' => [
                    'cargados' => $cargados,
                    'fallidos' => [
                        'inexistentes' => [
                            'cantidad' => count($errores['inexistentes']),
                            'detalle' => $errores['inexistentes']
                        ],
                        'existentes' => [
                            'cantidad' => count($errores['existentes']),
                            'detalle' => $errores['existentes']
                        ]
                    ],
                ]
            ]);
        }catch(Exception $ex){
            return $ex;
            return response()->json([
                'success' => false,
                'msg' => "Carga masiva de usuarios fallida.",
                'error' => $ex->getMessage()
            ]);
        }
    }

}
