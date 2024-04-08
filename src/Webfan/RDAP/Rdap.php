<?php declare(strict_types=1);

namespace Webfan\RDAP;


use Metaregistrar\RDAP\RdapException;
//use Metaregistrar\RDAP\Rdap as BaseRdapClient;
use Metaregistrar\RDAP\Responses\RdapAsnResponse;
use Metaregistrar\RDAP\Responses\RdapIpResponse;
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
    protected static $protocols = [
        'ipv4'   => [self::HOME => 'https://data.iana.org/rdap/ipv4.json', self::SEARCH => 'ip/'],
        'domain' => [self::HOME => 'https://data.iana.org/rdap/dns.json', self::SEARCH => 'domain/'],
        'ns'     => [self::HOME => 'https://data.iana.org/rdap/dns.json', self::SEARCH => 'nameserver/'],
        'ipv6'   => [self::HOME => 'https://data.iana.org/rdap/ipv6.json', self::SEARCH => 'ip/'],
        'asn'    => [self::HOME => 'https://data.iana.org/rdap/asn.json', self::SEARCH => 'autnum/'],

  
        'oid'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'oid/'],
  /*
         //relays?
        'webfinger'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => '.well-known/webfinger?resource='],
        '@'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => '@'],
  
        'oid-ip'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'oid-ip/'],
        'oid-connect'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'oid-connect/@'],
  
        'weid'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'weid/'],
        'iana-pen'    => [self::HOME => 'https://iana-pen.oid.zone/rdap/data/iana-pen.json', self::SEARCH => 'iana-pen/'],
        'cara'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'cara/'],
        'ra'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'ra/'],

  
        'service'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'service/provider/'],
        'container'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'service/container/'],
        'install'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'service/install/'],
        'ext'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'service/ext/'],
  
        'node'    => [self::HOME => 'https://iana-pen.oid.zone/rdap/data/oid.json', self::SEARCH => 'node/'],  
  
        'relay'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'relay/provider/'],
        'unbound'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'relay/unbound/'],
        'multi'    => [self::HOME => 'https://oid.zone/rdap/data/oid.json', self::SEARCH => 'relay/multi/']
        */
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

    /**
     *
     *
     * @param string $search
     *
     * @return \Metaregistrar\RDAP\Responses\RdapAsnResponse|\Metaregistrar\RDAP\Responses\RdapIpResponse|\Metaregistrar\RDAP\Responses\RdapResponse|null
     * @throws \Metaregistrar\RDAP\RdapException
     */
    public function search(string $search): ?RdapResponse {
        if (!isset($search) || ($search === '')) {
            throw new RdapException('Search parameter may not be empty');
        }

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
        $services  = $this->readRoot();

        foreach ($services as $service) {
			
						
            foreach ($service[0] as $number) {
                // ip address range match
                if (strpos($number, '-') > 0) {
                    [$start, $end] = explode('-', $number);
                    if (($parameter >= $start) && ($parameter <= $end)) {
                        // check for slash as last character in the server name, if not, add it
                        if ($service[1][0][strlen($service[1][0]) - 1] !== '/') {
                            $service[1][0] .= '/';
                        }
                        $rdap = file_get_contents($service[1][0] . self::$protocols[$this->protocol][self::SEARCH] . $search);

                        return $this->createResponse($this->getProtocol(), $rdap);
                    }
                } else {
                    // exact match
                    if ($number === $parameter) {
                        // check for slash as last character in the server name, if not, add it
                        if ($service[1][0][strlen($service[1][0]) - 1] !== '/') {
                            $service[1][0] .= '/';
                        }

                        $rdap = file_get_contents($service[1][0] . self::$protocols[$this->protocol][self::SEARCH] . $search);

                        return $this->createResponse($this->getProtocol(), $rdap);
                    }
                }
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
     * @return array
     */
    private function readRoot(): array {
        $rdap = file_get_contents(self::$protocols[$this->protocol][self::HOME]);
        $json = json_decode($rdap, false);
        $this->setDescription($json->description);
        $this->setPublicationdate($json->publication);
        $this->setVersion($json->version);

        return $json->services;
    }

    /**
     *
     *
     * @param string $protocol
     * @param string $json
     *
     * @return \Metaregistrar\RDAP\Responses\RdapResponse
     * @throws \Metaregistrar\RDAP\RdapException
     */
    protected function createResponse(string $protocol, string $json): RdapResponse {
        switch ($protocol) {
            case self::IPV4:
                return new RdapIpResponse($json);
            case self::ASN:
                return new RdapAsnResponse($json);
            default:
                return new RdapResponse($json);
        }
    }

    public function case(): void {
    }
}
