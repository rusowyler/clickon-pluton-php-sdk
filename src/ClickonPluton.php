<?php

namespace ClickonPluton;

use JWT;
use QueryPath;

/**
 * Class ClickonPluton
 * @package ClickonPluton
 */
class ClickonPluton
{
    /**
     * @var string $host Tracking host
     */
    private $host;

    /**
     * @var string $privateKey JWT encoding key
     */
    private $privateKey;

    private static $trackPath = '/click/';
    private static $openPath = '/open/';

    public function __construct($host, $privateKey){
        $this->host = rtrim($host, "/");
        $this->privateKey = $privateKey;
    }

    /**
     * @param $html
     * @param $campaign
     * @param $email
     * @return mixed
     */
    public function processHtml($html, $campaign, $email){
        $qp = qp($html);

        foreach($qp->find("a") as $a){
            $a->attr('href', self::encodeUrl(trim($a->attr('href')), $campaign, $email));
        }

        $qp->find("body")->append($this->generateOpenImage($campaign, $email));

        return $qp->html();
    }

    public function encodeUrl($url, $campaign, $email){
        return $this->host . self::$trackPath . JWT::encode(self::getRawPayload($campaign, $email, $url), $this->privateKey, 'HS256');
    }

    public function generateOpenImage($campaign, $email){
        return '<img src="' . $this->host . self::$openPath . JWT::encode(self::getRawPayload($campaign, $email), $this->privateKey, 'HS256') . '.gif" width="1" height="1" />';
    }

    private static function getRawPayload($campaign, $email, $url = null){

        $json = [ "campaign" => $campaign, "contact" => $email ];

        if(!empty($url)){
            $json['url'] = $url;
        }

        return $json;
    }
}