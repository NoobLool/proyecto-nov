<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class JWTAuth{

    public $key;

    public function __construct(){
        $this->key = 'clave-para-los-tokens-temporal';
    }

    public function signup($email, $password, $getToken = null){
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ['status' => 'error', 'message' => 'Usuario no encontrado'];
        }

        // Verificar contraseña si el usuario existe
        if (!Hash::check($password, $user->password)) {
            return ['status' => 'error', 'message' => 'Las credenciales de acceso son incorrectas'];
        }

        // Si el usuario es autenticado correctamente, generar el token
        $token = array(
            'sub' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'rol' => $user->rol,
            'id_sucursal' => $user->id_sucursal,
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60)
        );

        $jwt = JWT::encode($token, $this->key, 'HS256');
        $decoded = JWT::decode($jwt, new key ($this->key ,'HS256'));

        // Devolver el token codificado o decodificado según $getToken
        if (is_null($getToken)) {

            /**Se guarda el token en la base de datos */
            DB::table('user_tokens')->insert([
                'id_user' => $user->id,
                'token' => $jwt,
                'is_active' => true,
                'expires_at' => date('Y-m-d H:i:s', $token['exp'])
            ]);

            return response()->json([
                'message' => 'Login correcto',
                'token' =>  $jwt
        ], 200);

            //return $jwt;  // Devuelve el token codificado si $getToken es null
        } else {
            return $decoded;  // Devuelve el token decodificado si $getToken no es null
        }
    }


    //La función checkToken, permite verificar si el toquen de accesso es valido
    public function checkToken($jwt, $getIdentity = false){
        
        /**Se verifica que exista el token y que este activo */
        $tokenRecord = DB::table('user_tokens')->where('token', $jwt)->where('is_active', true)->first();
        if(!$tokenRecord){
            $auth = false;/**Token inexistente o inactivo */
        }
        
        /*Con esto, checkToken decodifica la clave de acceso, en caso de no tener ningun valor
        o tener un valor incorrecto, se regresa un valor false ya que no cumple con la condición
        para permitir el acceso*/
        try{
            $decoded = JWT::decode($jwt, new key ($this->key, 'HS256'));

        }catch(\UnexpectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }

        /** Se comprueba que la variable decoded cumpla con las diferentes codiciones,
         * primero que no sea un valor vacio, cumpla con las condiciones del objeto JwtAuth
         * y contenga la identificación del usuario que genero el token, para así devolver
         * el valor de $auth como verdadero y permita el acceso al usuario
        */
        if(isset($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        /**Si se cumplen todas las codiciones para convertir a $getIdentity en true, se 
         * devuevle el valor de $decoded que es el token decodificado
         */
        if($getIdentity){
            return isset($decoded) ? User::find($decoded->sub) : null;
        }

        return $auth;
    }
}
