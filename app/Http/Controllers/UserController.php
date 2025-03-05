<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JwtAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/user/register",
     *     summary="Registro de usuario",
     *     description="Se registra un usuario en el sistema, esto por parte del administrador general.",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","rol","id_sucursal"},
     *             @OA\Property(property="name", type="string", example="Juan Perez"),
     *             @OA\Property(property="email", type="string", example="ejemplo12@gmail.com"),
     *             @OA\Property(property="password", type="string", example="12345"),
     *             @OA\Property(property="rol", type="string", example="user"),
     *             @OA\Property(property="id_sucursal", type="number", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Respuesta de éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="usuario creado con exito")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Usuario duplicado o Error al crear el usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El usuario ya existe o Usuario no creado")
     *         )
     *     )
     * )
    */
    public function register(Request $request){
        //Recoger post
        $json = $request->input('json',null);
        $params = json_decode($json);

        $name = (!is_null($json) && isset($params->name)) ? $params->name : null;
        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null;
        $rol= (!is_null($json) && isset($params->rol)) ? $params->rol : null;
        $id_sucursal = (!is_null($json) && isset($params->id_sucursal)) ? $params->id_sucursal : null;

        if(!is_null($email) && !is_null($password) && !is_null($name)){
            
            //Crear el usuario
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->rol = $rol;
            $user->id_sucursal = $id_sucursal;

            $pwd = Hash::make($password);
            $user->password = $pwd;

            //Comprobar usuario dupilcado
            $isset_user = User::where('email',$email)->count();

            if ($isset_user == 0) {
                //Guardar usuario
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Usuario registrado con exito'
                );
            } else {
                //Usuario existente
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El usuario ya existe'
                );
            }
            
            if($user->rol == 'deliver'){
                return response()->json([
                    'usuario' => $user,
                    'status' => 'success'
                ], 200);
            }

        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Usuario no creado'
            );
        }

        return response()->json($data, 200);
    }

    /**
     * @OA\Post(
     *     path="/user/login",
     *     summary="Iniciar sesión",
     *     description="Inicia sesión con un email y una contraseña y devuelve un token JWT.",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="ejemplo12@gmail.com"),
     *             @OA\Property(property="password", type="string", example="12345"),
     *             @OA\Property(property="gettoken", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Respuesta de éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login correcto"),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Credenciales incorrectas")
     *         )
     *     )
     * )
     */

    public function login(Request $request){
        //Metodo de autenticación de usuario
        $jwtAuth = new JwtAuth();

        //Se verifica que los datos se reciban por Json, y a su vez se decodifica el archivo para su lectura
        $json = $request->input('json', null);
        $params = json_decode($json);

        //Se verifica que los datos siguientes vengan en el Json
        $email = (!is_null($json) && isset($params->email)) ? $params->email: null;
        $password = (!is_null($json) && isset($params->password)) ? $params->password: null;
        $getToken = (!is_null($json) && isset($params->gettoken)) ? $params->gettoken: true;
    
        //Cifrar password
        $pwd = hash('sha256', $password);

        /**Si los datos del Json existen y son correctos, se llama a la función signup de JwtAuth
         * que verifica que existan los datos del usuario
        */
        if(!is_null($email) && !is_null($password) && ($getToken == null || $getToken == 'false')){
            $signup = $jwtAuth->signup($email, $password);
        
        //Por ultimo se checa si los datos de $getToken son correctos
        }elseif($getToken != null){
            $signup = $jwtAuth->signup($email,$password,$getToken);
        
        //En caso de error, se manda mensaje
        }else{
            $signup = array(
                'status' => 'error',
                'message' => 'Envía tus datos por post'
            );
        }

        return response()->json($signup, 200);
    }

    /**
     * @OA\Post(
     *     path="/user/logout",
     *     summary="Cierre de sesión",
     *     description=" Cierre de sesión de usuario, eliminando así el Token de acceso.",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="ejemplo12@gmail.com"),
     *             @OA\Property(property="password", type="string", example="12345"),
     *             @OA\Property(property="gettoken", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Respuesta de éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login correcto"),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Credenciales incorrectas")
     *         )
     *     )
     * )
     */
    /**Función para el cierre de sesión de los usuarios */
    public function logout(Request $request){
        /**Se obtiene el token de acceso */
        $jwt = $request->bearerToken();

        if(!$jwt){
            return response()->json([
                'message' => 'Token no valido o sesion expirada'
            ], 401);
        }

        /**Se marca el token como inactivo */
        $cambioToken = DB::table('user_tokens')
            ->where('token', $jwt)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        if($cambioToken === 0){
            return response()->json([
                'message' => 'Token no valido o expirado'
            ], 401);
        }

        return response()->json(['message' => 'Cierre de sesion exitoso']);

    }

}