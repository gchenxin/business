<?php

namespace App\Traits;

trait SendRequest{

    /**
     * 返回不需要验证的接口列表
     * @return array
     */
    public static function send($url, $params){
        if($params){
            $url .= "?" . http_build_query($params);
        }
        $context_options = [
            'http'=>[
                'method'=>"GET",
                "header"=>[
                    'Accept: application/json',
                    'Content-Type: application/json;charset=UTF-8',
                    "User-Agent: Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)"
                ]
            ],
            "ssl" => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];
        $response = file_get_contents($url,false,stream_context_create($context_options));
        return json_decode($response, true);
    }
}