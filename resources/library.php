<?php
namespace MEMEBoard{
	if ( class_exists('\\MEMEBoard\\Utils') === false ){
		class Utils{
			/**
			* Returns an error response to the client.
			*
			* @param int $code An integer number containing the code that represent the error thrown.
			* @param string $description A string containing a textual description of the error.
			*
			* @return JsonResponse An isntance of the class "Illuminate\Http\JsonResponse" representing the response that will be sent to the client.
			*/
			public static function returnError(int $code, string $description): \Illuminate\Http\JsonResponse{
				$data = array(
					'result' => 'error',
					'code' => ( $code === NULL ? 0 : $code ),
					'description' => ( $description === NULL ? '' : $description )
				);
				return \Illuminate\Support\Facades\Response::json($data, http_response_code());
			}
			
			/**
			* Returns an error response to the client.
			*
			* @param int $code An integer number containing the code that represent the error thrown.
			* @param string $description A string containing a textual description of the error.
			*
			* @return JsonResponse An isntance of the class "Illuminate\Http\JsonResponse" representing the response that will be sent to the client.
			*/
			public static function returnSuccess(int $code, string $description, array $data = NULL): \Illuminate\Http\JsonResponse{
				$data = $data === NULL ? array(
					'result' => 'success',
					'code' => ( $code === NULL ? 0 : $code ),
					'description' => ( $description === NULL ? '' : $description )
				) : array(
					'result' => 'success',
					'code' => ( $code === NULL ? 0 : $code ),
					'description' => ( $description === NULL ? '' : $description ),
					'data' => $data
				);
				return \Illuminate\Support\Facades\Response::json($data, http_response_code());
			}
			
			/**
			* Creates a string representation of a number.
			*
			* @param int $counter An integer number greater or equal than zero.
			*
			* @return string A string representation of the counter value.
			*/
			public static function stringifyCounterValue(int $counter): string{
				if ( $counter === NULL || $counter <= 0 ){
					return '0';
				}
				$counter = abs($counter);
				if ( floor( $counter / 1000 ) > 0 ){
					$counter = $counter / 1000;
					if ( floor( $counter / 1000 ) > 0 ){
						$counter = $counter / 1000;
						if ( floor( $counter / 1000 ) > 0 ){
							$counter = $counter / 1000;
							if ( floor( $counter / 1000 ) > 0 ){
								$counter = $counter / 1000;
								return floor( $counter / 1000 ) > 0 ? ( floor( $counter / 1000 ) . ' P' ) : ( floor($counter) . ' T' );
							}
							return floor($counter) . ' G';
						}
						return floor($counter) . ' M';
					}
					return floor($counter) . ' K';
				}
				return strval($counter);
			}
			
			/**
			* Return an error response to the client telling to it that no authenticated user has been found or that the authenticated user is not an admin.
			*
			* @param bool $admin If set to "true", will be told to the client that no admin user authenticated has been found, otherwise will be told that no user has been found, even a normal user.
			*
			* @return JsonResponse An isntance of the class "Illuminate\Http\JsonResponse" representing the response that will be sent to the client.
			*/
			public static function returnUnauthorizedError(bool $admin = false): \Illuminate\Http\JsonResponse{
				if ( $admin === true ){
					http_response_code(405);
					return self::returnError(405, 'This feature is reserved to admins only.');
				}
				http_response_code(403);
				return self::returnError(403, 'You need to be logged in before using this feature.');
			}
		}
	}
}