<?php 

namespace App\Libraries;
//
//use Illuminate\Support\Facades\Log;

class HTTPHelper {

    public function http_fetch($args) {
        // Parse Args
        $url = $args['url'];
        $verb=isset($args['verb'])?$args['verb']:'GET';
        $request_data=$args['data'];
        $username = isset($args['username'])?$args['username']:null;
        $password = isset($args['password'])?$args['password']:null;
        $mime_type = isset($args['mime_type'])?strtolower($args['mime_type']):'application/x-www-form-urlencoded';
        $timeout = isset($args['timeout'])?$args['timeout']:300; // Default to 5 minute timeout;
        $headers = null;
        if (isset($args['headers'])) {
            $headers = '';
            foreach($args['headers'] as $key => $value) {
                $headers.=$key.": ".$value."\r\n";
            }
        }

        // Build HTTP Request
        $request_config = [];
        $request_config['ignore_errors'] = true;
        $request_config['method'] = $verb;
        $request_config['header'] = "Content-type: ".$mime_type."\r\n"."User-Agent: rest\r\n";
        $request_config['timeout'] = $timeout;
        if (!is_null($username)) {
            $request_config['header'] .= "Authorization: Basic ".base64_encode($username.':'.$password)."\r\n";
        }
        if (isset($args['accept_mime_type'])) {
            $request_config['header'] .= "Accept: ".$args['accept_mime_type']."\r\n";
        } else if (isset($args['accept_mime_types']) && is_array($args['accept_mime_types'])) {
            $request_config['header'] .= "Accept: ".implode(', ',$args['accept_mime_types'])."\r\n";
        }
        if (!is_null($headers)) {
            $request_config['header'] .= $headers;
        }
        if ($verb == 'GET') {
            $url_parts = parse_url($url);
            $url_parts_query = [];
            if(array_key_exists('query', $url_parts)){
                parse_str($url_parts['query'],$url_parts_query);
            }
            if(array_key_exists('scheme', $url_parts) && array_key_exists('host', $url_parts)) {
                $url = $url_parts['scheme'].'://'.$url_parts['host'];
                if(array_key_exists('port', $url_parts)) {
                    $url .= ':'.$url_parts['port'];
                }
                if(array_key_exists('path', $url_parts)) {
                    $url .= str_replace(' ', '%20', $url_parts['path']);
                }
                $url .= '?'.http_build_query(array_merge($request_data,$url_parts_query));
            }else{
                abort(400);             
            }
        } else {
            if ($mime_type === 'application/x-www-form-urlencoded' && (is_array($request_data) || is_object($request_data))) {
                $request_config['content'] = http_build_query($request_data);
            } else if ($mime_type === 'application/json' && (is_array($request_data) || is_object($request_data))) {
                $request_config['content'] = json_encode($request_data);
            } else {
                $request_config['content'] = $request_data;
            }
        }
        $context = stream_context_create(['http' =>$request_config]);
        $response_data = @file_get_contents($url, false, $context);
        
        // Failed -- Return 502 Bad Gateway
        if ($response_data === FALSE || !isset($http_response_header)) {
            if (class_exists('Log')) {
                Log::error('HTTPHelper - '.$username.'@'.$password.' - Failed to Fetch Data For URL: '.$url);
            }
            return ['code'=>502,'headers'=>[],'content'=>''];
        }

        // Check if the data we got back was JSON Formatted
        $response_code = '444'; // This should be overwritten with an actual response code
        foreach($http_response_header as $header) {
            if (stristr($header, 'Content-Type: application/json')) {
                $response_data = json_decode($response_data,true);
            } else if (stristr($header, 'HTTP/')) {
                $header_exploded = explode(' ',$header);
                $response_code = $header_exploded[1];
            }
        }
        if ($response_code[0] === '5') {
            if (class_exists('Log')) {
                Log::error($response_code.' response code received for URL: '.$url);
            }
        }
        return ['content'=>$response_data,'code'=>$response_code,'headers'=>$http_response_header];
    }
}