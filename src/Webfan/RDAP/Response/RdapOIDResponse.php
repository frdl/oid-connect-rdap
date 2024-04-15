<?php

namespace Webfan\RDAP\Response{
use Metaregistrar\RDAP\Responses\RdapResponse as BaseRdapResponse;

use Metaregistrar\RDAP\Data\RdapConformance;
use Metaregistrar\RDAP\Data\RdapEntity;
use Metaregistrar\RDAP\Data\RdapEvent;
use Metaregistrar\RDAP\Data\RdapLink;
use Metaregistrar\RDAP\Data\RdapNameserver;
use Metaregistrar\RDAP\Data\RdapNotice;
use Metaregistrar\RDAP\Data\RdapObject;
use Metaregistrar\RDAP\Data\RdapRemark;
use Metaregistrar\RDAP\Data\RdapSecureDNS;
use Metaregistrar\RDAP\Data\RdapStatus;
use Metaregistrar\RDAP\RdapException;

class RdapOIDResponse extends BaseRdapResponse {
    /**
     * @var string|null
     */
    protected $objectClassName;
    /**
     * @var string|null
     */
    protected $ldhName ;
    /**
     * @var string
     */
    protected $handle = '';
    /*
    * @var  string
    */
    protected $name = '';
    /**
     * @var string
     */
    protected $type = '';
    /**
     * @var null|RdapConformance[]
     */
    protected $rdapConformance;
    /**
     * @var null|RdapEntity[]
     */
    protected $entities;
    /**
     * @var null|RdapLink[]
     */
    protected $links;
    /**
     * @var null|RdapRemark[]
     */
    protected $remarks;
    /**
     * @var null|RdapNotice[]
     */
    private $notices;
    /**
     * @var null|RdapEvent[]
     */
    protected $events;
    /**
     * @var null|string
     */
    protected $port43;
    /**
     * @var null|RdapNameserver[]
     */
    protected $nameservers;
    /**
     * @var null|RdapStatus[]
     */
    protected $status;
    /**
     * @var null|RdapSecureDNS[]
     */
    protected $secureDNS;
    /**
     * @var int
     */
    protected $errorCode;

    /**
     * @var string
     */
    protected $title;

    /**
     * RdapResponse constructor.
     *
     * @param string $json
     *
     * @throws \Metaregistrar\RDAP\RdapException
     */
    public function __construct(string $json) {
        if ($data = json_decode($json, true)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    // $value is an array
                    foreach ($value as $k => $v) {
                        $this->{$key}[] = RdapObject::KeyToObject($key, $v);
                    }
                } else {
                    // $value is not an array, just create a var with this value (startAddress endAddress ipVersion etc etc)
                    $this->{$key} = $value;
                }
            }
        } else {
            throw new RdapException('Response object could not be validated as proper JSON');
        }
    }

   

}

}//ns
