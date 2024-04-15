<?php 
declare(strict_types=1);

namespace Webfan\RDAP{


use Metaregistrar\RDAP\RdapException;
//use Metaregistrar\RDAP\Rdap as BaseRdapClient;
use Metaregistrar\RDAP\Responses\RdapAsnResponse;
use Metaregistrar\RDAP\Responses\RdapIpResponse;
use Webfan\RDAP\Response\RdapOIDResponse;
use Metaregistrar\RDAP\Responses\RdapResponse;
	
	
	
	
	
	
	
	
	
	
class Rdap // extends BaseRdapClient
{
  
    public const ASN    = 'asn';
    public const IPV4   = 'ipv4';
    public const IPV6   = 'ipv6';
    public const NS     = 'ns';
    public const DOMAIN = 'domain';
    public const SEARCH = 'search';
    public const HOME   = 'home'; 
    public const SERVICES   = 'services'; 
 
  
    public const OID = 'oid';
  /*
    public const WEID = 'weid';
    public const PEN = 'iana-pen';
    public const CARA = 'cara';
    public const RA = 'ra';
    public const SERVICE = 'service';
    public const NODE = 'node';
    public const WEBFINGER = 'webfinger';
    public const HANDLE = '@';
  
   public const UNBOUND = 'unbound';
   public const MULTI = 'multi';
  
   public const CONNECT = 'oid-connect';
  */

    public $userAgent = 'RDAPClient/0.1 (Webfan) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/0.0.1';
	
    protected static $protocols = [
        'ipv4'   => [self::HOME => 'https://data.iana.org/rdap/ipv4.json', self::SEARCH => 'ip/', self::SERVICES => [] ],
        'domain' => [self::HOME => 'https://data.iana.org/rdap/dns.json', self::SEARCH => 'domain/', self::SERVICES => [] ],
        'ns'     => [self::HOME => 'https://data.iana.org/rdap/dns.json', self::SEARCH => 'nameserver/', self::SERVICES => [] ],
        'ipv6'   => [self::HOME => 'https://data.iana.org/rdap/ipv6.json', self::SEARCH => 'ip/', self::SERVICES => [] ],
        'asn'    => [self::HOME => 'https://data.iana.org/rdap/asn.json', self::SEARCH => 'autnum/', self::SERVICES => [] ],  
        'oid'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'oid/', self::SERVICES => [] ],
    ];
	
 
	
    private $protocol;
    private $publicationdate = '';
    private $version         = '';
    private $description     = '';

    /**
     * Rdap constructor.
     *
     * @param string $protocol
     *
     * @throws \Metaregistrar\RDAP\RdapException
     */
    public function __construct(string $protocol) {
    //    if (($protocol !== self::ASN) && ($protocol !== self::IPV4) && ($protocol !== self::IPV6) && ($protocol !== self::DOMAIN)) {
    //        throw new RdapException('Protocol ' . $protocol . ' is not recognized by this rdap client implementation');
    //    }
        if (!isset(self::$protocols[$protocol])) {
            throw new RdapException('Protocol ' . $protocol . ' is not recognized by this rdap client implementation');
        }
        $this->protocol = $protocol;
    }

    public function addService(string $protocol, string | array $servers) : self {
        if (!isset(self::$protocols[$protocol])) {
            throw new RdapException('Protocol ' . $protocol . ' is not recognized by this rdap client implementation');
        }	    
        self::$protocols[$protocol][self::SERVICES][] = $servers;
	    
        return $this;
    }


    public function readServices(?string $protocol = null): array {
	if(!is_string($protocol)){
           $protocol = $this->protocol;
	}
        $services = [];
	$servers =  self::$protocols[$protocol][self::SERVICES];  
	foreach($servers as $s){
           if(is_string($s)){
             $as = @file_get_contents($s);
	     $s = false === $as ? [] : json_decode($as, false);	   
	     $s=(array)$s;
	     if(isset($s['services'])){
               $s=$s['services'];
	     }
	   } 		
	  foreach($s as $_s){
              array_push($services, $_s);
	   }
	}
     return $services;
    }

	
    /**
     * @return array
     */
    public function readRoot(?string $protocol = null): array {
	if(!is_string($protocol)){
           $protocol = $this->protocol;
	}
		
        $rdap = file_get_contents(self::$protocols[$protocol][self::HOME]);
        $json = json_decode($rdap, false);
        $this->setDescription($json->description);
        $this->setPublicationdate($json->publication);
        $this->setVersion($json->version);

        return $json->services;
    }


	
    /**
     * @return string
     */
    public function getPublicationdate(): string {
        return $this->publicationdate;
    }

    /**
     * @param string $publicationdate
     */
    public function setPublicationdate(string $publicationdate): void {
        $this->publicationdate = $publicationdate;
    }

    /**
     * @return string
     */
    public function getVersion(): string {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void {
        $this->description = $description;
    }
   
	
public function siteURL(){
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'].'/';
    return $protocol.$domainName. $_SERVER['REQUEST_URI'];
}


   public function rdap(string $search)  {
	  $skipRefererBounce = true;   
	  $searchLocalOnly = false;	
	if($_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'] 
	   || (isset( $_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['SERVER_ADDR'] ===  $_SERVER['HTTP_X_FORWARDED_FOR'] )
	   || (isset( $_SERVER['HTTP_CLIENT_IP']) && $_SERVER['SERVER_ADDR'] ===  $_SERVER['HTTP_CLIENT_IP'] )){
          $skipRefererBounce = true;
	  $searchLocalOnly = isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']=== $this->siteURL();	
	}
     return $this->searchServers($search, $searchLocalOnly,$skipRefererBounce);
   }

   public function search(string $search, ?bool $searchLocalOnly = false, ?bool $skipRefererBounce = true){
	  $skipRefererBounce = false;   
	  $searchLocalOnly = false;	
     return $this->searchServers($search, $searchLocalOnly,$skipRefererBounce);
   }
    /**
     *
     *
     * @param string $search
     *
     * @return \Metaregistrar\RDAP\Responses\RdapAsnResponse|\Metaregistrar\RDAP\Responses\RdapIpResponse|\Metaregistrar\RDAP\Responses\RdapResponse|null
     * @throws \Metaregistrar\RDAP\RdapException
     */
    public function searchServers(string $search, ?bool $searchLocalOnly = false, ?bool $skipRefererBounce = true)  {
        if (!isset($search) || ($search === '')) {
            throw new RdapException('Search parameter may not be empty');
        }


	 $userAgent = $this->userAgent;
	$ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';//$userAgent;
        $referer = $this->siteURL();
        $options = array(
           'http'=>array(
              'method'=>"GET",
              'header'=>"Accept-language: en\r\n" 
		   // ."Cookie: foo=bar\r\n"
                 . "User-Agent: $userAgent\r\n"  
		 ."Referer: $referer\r\n"
            )
        );

	   
         $context = stream_context_create($options);
	    
        $search = trim($search);
      /*
        if ($this->getProtocol() !== self::ASN && (!is_string($search)) && in_array($this->getProtocol(), [
              self::DOMAIN, self::NS, self::IPV4, self::IPV6, self::OID, self::PEN,
              self::CARA, self::SERVICE, self::NODE, self::HANDLE
                                                                                    ], true)) {
            throw new RdapException('Search parameter must be a string for ipv4, ipv6, domain or nameserver searches');
        }

        if ((!is_numeric($search)) && ($this->getProtocol() === self::ASN)) {
            throw new RdapException('Search parameter must be a number or a string with numeric info for asn searches');
        }
*/
        $parameter = $this->prepareSearch($search);
        $services  = true===$searchLocalOnly ? [] : $this->readRoot();
	
	foreach($this->readServices($this->protocol) as $s){
          array_push($services, $s);
	}

		
		
        $moreServices = [];
	    
        foreach ($services as $service) {
									
            foreach ($service[0] as $number) { 
		    // check for slash as last character in the server name, if not, add it
                        if ($service[1][0][strlen($service[1][0]) - 1] !== '/') {
                            $service[1][0] .= '/';
                        }
		    $rdapServerUrlBase = $service[1][0] . self::$protocols[$this->protocol][self::SEARCH];
		    $rdapServerUrlForSearch = $rdapServerUrlBase. $search;

                   if($skipRefererBounce && isset($_SERVER['HTTP_REFERER'])
		      && ($_SERVER['HTTP_REFERER']=== $this->siteURL() 
			  || $_SERVER['HTTP_REFERER']===$rdapServerUrlBase
			  || $_SERVER['HTTP_REFERER']===$rdapServerUrlForSearch 
			  || str_contains($ua, 'rdap')
			  || str_contains($ua, 'webfan')
			  || str_contains($ua, 'frdlweb')
			 )
		     ){
					   
		     continue;
		   }
      
				
		
				
                if (strpos($number, '-') > 0) {
                    [$start, $end] = explode('-', $number);
                    if (($parameter >= $start) && ($parameter <= $end)) {
                        $moreServices[$number]= $rdapServerUrlForSearch;	
                    }
                } elseif ($number === $parameter) {
			$moreServices[$number]= $rdapServerUrlForSearch;	
                }elseif($number === substr($search, 0, strlen($number)) ){
                      $moreServices[$number]= $rdapServerUrlForSearch;					
		}
            }//$service[0]
        }//$services

         krsort($moreServices);
         foreach($moreServices as $number => $url){          			
		 $rdap = @file_get_contents($url, false, $context); 
			 
		 if($rdap){                   
		    return $this->createResponse($this->getProtocol(), $rdap);				
		 }		
	 }	    
        return null;
    }

    /**
     * @return string
     */
    public function getProtocol(): string {
        return $this->protocol;
    }

    private function prepareSearch(string $string): string {
        switch ($this->getProtocol()) {
            case self::IPV4:
                [$start] = explode('.', $string);

                return $start . '.0.0.0/8';
            case self::DOMAIN:
                $extension = explode('.', $string, 2);

                return $extension[1];
            default:
                return $string;
        }
    }


    /**
     *
     *
     * @param string $protocol  RdapOIDResponse
     * @param string $json
     *
     * @return \Metaregistrar\RDAP\Responses\RdapResponse
     * @throws \Metaregistrar\RDAP\RdapException
     */
    protected function createResponse(string $protocol, string $json) {
	 	return json_decode($json);
	 /*
        switch ($protocol) {
            case self::IPV4:
                return new RdapIpResponse($json);
            case self::ASN:
                return new RdapAsnResponse($json);
		 	case self::OID:
	    		return new RdapOIDResponse($json);
            default:
                return new RdapResponse($json);
        }
		*/
    }

    public function case(): void {
    }
}
	
}//ns
