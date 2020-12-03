<?php

namespace dbSYNC\mensajes;

use dbSYNC\conf\telegram1;

class telegram{

    /**
     * Enviar mensaje por telegram.
    */
    public static function sendMessage($text){
    
        $TELEGRAM = "https://api.telegram.org:443/bot" . telegram1::$token; 
        
        $query = http_build_query(array(
            'chat_id'   => telegram1::$sala,
            'text'      => $text,
            'parse_mode'=> "Markdown", // Optional: Markdown | HTML
        ));
        
        $response = file_get_contents("$TELEGRAM/sendMessage?$query");
        return $response;
    
    }

}