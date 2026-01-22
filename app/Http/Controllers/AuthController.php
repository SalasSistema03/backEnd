<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\usuarios_y_permisos\Usuario;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Exception;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Autentica un usuario y genera un token JWT
     *
     * Valida las credenciales recibidas, verifica manualmente el usuario
     * y su contraseña en texto plano, y genera un token JWT si la autenticación
     * es exitosa.
     *
     * @param Request $request solicitud HTTP con username y password
     *
     * @return \Illuminate\Http\JsonResponse respuesta JSON con el token o error
     * @access public
     */
    public function login(Request $request)
    {
        /* 
         * Se crea el validador para comprobar que los campos requeridos
         * existan y cumplan con las reglas de longitud y tipo
         */
        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'required|string|min:3|max:50',
                'password' => 'required|string|min:3|max:50',
            ],
            [
                'username.required' => 'El campo usuario es obligatorio',
                'password.required' => 'El campo contraseña es obligatorio',
                'username.min' => 'El campo usuario debe tener al menos 3 caracteres',
                'password.min' => 'El campo contraseña debe tener al menos 3 caracteres',
                'username.max' => 'El campo usuario debe tener como maximo 50 caracteres',
                'password.max' => 'El campo contraseña debe tener como maximo 50 caracteres',
            ]
        );

        /*
         * Si la validación falla, se retorna una respuesta JSON
         * con los errores y un código HTTP 400 (Bad Request)
         */
        if ($validator->fails()) {
            /* return response()->json(['error' => $validator->errors()], response::HTTP_BAD_REQUEST); */
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
         * Se extraen únicamente las credenciales necesarias
         * desde la solicitud HTTP
         */
        $credentials = $request->only(['username', 'password']);

        /*
         * Se busca el usuario en la base de datos por username
         * (autenticación manual sin hashing)
         */
        $user = Usuario::where('username', $credentials['username'])->first();


        /*
         * Se valida que el usuario exista y que la contraseña coincida
         * En caso contrario, se devuelve un error de autenticación
         */
        if (!$user || $user->password !== $credentials['password']) {
            /* return response()->json(['error' => 'Los datos ingresados son incorrectos'], response::HTTP_UNAUTHORIZED); */
            return response()->json([
                'message' => 'Los datos ingresados son incorrectos'
            ], response::HTTP_UNAUTHORIZED);
        }

        /*
         * Se genera manualmente el token JWT a partir del usuario autenticado
         */
        $token = JWTAuth::fromUser($user);

        /*
         * Se retorna la respuesta estándar con el token generado
         */
        return $this->respondWithToken($token);
    }

    /**
     * Devuelve una respuesta JSON con el token JWT
     *
     * Construye la estructura estándar del token incluyendo
     * tipo, tiempo de expiración y código HTTP correspondiente.
     *
     * @param string $token token JWT generado
     *
     * @return \Illuminate\Http\JsonResponse respuesta JSON con datos del token
     * @access protected
     */
    protected function respondWithToken($token)
    {
        /*
         * Se retorna la respuesta JSON con la información
         * necesaria para la autenticación basada en JWT
         */
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ], response::HTTP_OK);
    }

    /**
     * Registra un nuevo usuario en el sistema
     *
     * Valida los datos recibidos, verifica que el username
     * no exista previamente y crea el usuario en la base de datos.
     *
     * @param Request $request solicitud HTTP con datos del usuario
     *
     * @return \Illuminate\Http\JsonResponse respuesta JSON con estado de creación
     * @access public
     */
    public function register(Request $request)
    {
        /*
         * Se validan los campos obligatorios para la creación
         * del usuario
         */
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'admin' => 'required|boolean'
        ]);

        /*
         * Si la validación falla, se retorna un error con
         * código HTTP 400
         */
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], response::HTTP_BAD_REQUEST);
        }

        /*
         * Se verifica si ya existe un usuario con el mismo username
         */
        $exists = Usuario::where('username', $request->input('username'))->first();

        if (!$exists) {
            /*
             * Se crea el nuevo usuario con los datos recibidos
             */
            $new = Usuario::create([
                'name' => $request->input('name'),
                'username' => $request->input('username'),
                'password' => $request->input('password'),
                'admin' => $request->input('admin'),
            ]);

            /*
             * Si la creación falla, se devuelve un error interno
             */
            if (!$new) {
                return response()->json(['error' => 'error al crear el usuario'], response::HTTP_INTERNAL_SERVER_ERROR);
            }

            /*
             * Usuario creado correctamente
             */
            return response()->json(['message' => 'usuario creado correctamente'], response::HTTP_CREATED);
        } else {
            /*
             * El usuario ya existe en el sistema
             */
            return response()->json(['error' => 'usuario ya existe'], response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Obtiene la información del usuario autenticado
     *
     * Devuelve el usuario asociado al token JWT actual.
     *
     * @return \Illuminate\Http\JsonResponse datos del usuario autenticado
     * @access public
     */
    public function me()
    {
        $user = auth('api')->user();
        /*
         * Se retorna el usuario autenticado mediante el guard api
         */
        return response()->json(auth('api')->user());
    }

    /**
     * Cierra la sesión del usuario autenticado
     *
     * Invalida el token JWT actual evitando su reutilización.
     *
     * @return \Illuminate\Http\JsonResponse respuesta con estado del logout
     * @access public
     */
    public function logout()
    {
        try {
            /*
             * Se obtiene el token actual desde la petición
             */
            $token = JWTAuth::getToken();
            /*
             * Si no existe token, se retorna un error
             */
            if (!$token) {
                return response()->json(['error' => 'token invalido'], response::HTTP_BAD_REQUEST);
            }
            /*
             * Se invalida el token para cerrar la sesión
             */
            JWTAuth::invalidate($token);
            return response()->json(['message' => 'logout correcto'], Response::HTTP_OK);
        } catch (TokenInvalidException $e) {
            /*
             * El token ya no es válido
             */
            return response()->json(['error' => 'logout correcto'], Response::HTTP_UNAUTHORIZED);
        } catch (Exception $e) {
            /*
             * Error inesperado durante el proceso de logout
             */
            return response()->json(['error' => 'No se pudo cerrar sesion'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Renueva el token JWT actual
     *
     * Genera un nuevo token y revoca el anterior.
     *
     * @return \Illuminate\Http\JsonResponse respuesta JSON con nuevo token
     * @access public
     */
    public function refresh()
    {
        try {
            /*
             * Se obtiene el token actual
             */
            $token = JWTAuth::getToken();
            /*
             * Si no existe token, se retorna error
             */
            if (!$token) {
                return response()->json(['error' => 'Token no encontrado'], Response::HTTP_BAD_REQUEST);
            }
            /*
             * Se genera un nuevo token a partir del actual
             */
            $nuevo_token = JWTAuth::refresh($token);

            /*
             * Se invalida el token anterior
             */
            JWTAuth::invalidate($token);

            /*
             * Se retorna el nuevo token en formato estándar
             */
            return $this->respondWithToken($nuevo_token);
        } catch (TokenInvalidException $e) {
            /*
             * El token no es válido
             */
            return response()->json(['error' => 'Token invalido'], Response::HTTP_UNAUTHORIZED);
        } catch (Exception $e) {
            /*
             * Error inesperado durante la renovación del token
             */
            return response()->json(['error' => 'No se pudo cerrar sesion'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
