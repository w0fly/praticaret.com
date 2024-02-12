<?php
/**
 * Jaxl (Jabber XMPP Library)
 *
 * Copyright (c) 2009-2012, Abhinav Singh <me@abhinavsingh.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in
 * the documentation and/or other materials provided with the
 * distribution.
 *
 * * Neither the name of Abhinav Singh nor the names of his
 * contributors may be used to endorse or promote products derived
 * from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 */

class XEP0206 extends XMPPXep implements JAXLClientBase
{
    const NS_HTTP_BIND = 'http://jabber.org/protocol/httpbind';
    const NS_BOSH = 'urn:xmpp:xbosh';

    private $mch = null;
    public $chs = array();
    private $recv_cb = null;
    
    public $rid = null;
    public $sid = null;
    private $hold = 1;
    private $wait = 30;
    
    private $restarted = false;
    
    public $headers = array(
        'Accept-Encoding: gzip, deflate',
        'Content-Type: text/xml; charset=utf-8'
    );
    
    //
    // abstract method
    //
    
    public function init()
    {
        $this->mch = curl_multi_init();
        
        return array(
            
        );
    }

    //
    // event callbacks
    //

    /**
     * @param JAXLXmlAccess|string $data
     */
    public function send($data)
    {
        if ($data instanceof JAXLXmlAccess) {
            $data = $data->to_string();
        } else {
            if (substr($data, 0, 15) == '<stream:stream ') {
                $this->restarted = true;

                $data = new JAXLXml('body', self::NS_HTTP_BIND, array(
                    'sid' => $this->sid,
                    'rid' => ++$this->rid,
                    'to' => (($this->jaxl && $this->jaxl->jid)
                             ? $this->jaxl->jid->domain
                             : $this->jaxl->cfg['domain']),
                    'xmpp:restart' => 'true',
                    'xmlns:xmpp' => self::NS_BOSH
                ));

                $data = $data->to_string();
            } elseif (substr($data, 0, 16) == '</stream:stream>') {
                $data = new JAXLXml('body', self::NS_HTTP_BIND, array(
                    'sid' => $this->sid,
                    'rid' => ++$this->rid,
                    'type' => 'terminate'
                ));

                $data = $data->to_string();
            } else {
                $data = $this->wrap($data);
            }
        }
        JAXLLogger::debug("posting to ".$this->jaxl->cfg['bosh_url']." body ".$data);
        
        $this->chs[$this->rid] = curl_init($this->jaxl->cfg['bosh_url']);
        curl_setopt($this->chs[$this->rid], CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->chs[$this->rid], CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($this->chs[$this->rid], CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($this->chs[$this->rid], CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->chs[$this->rid], CURLOPT_VERBOSE, false);
        curl_setopt($this->chs[$this->rid], CURLOPT_POST, 1);
        curl_setopt($this->chs[$this->rid], CURLOPT_POSTFIELDS, $data);
        
        curl_multi_add_handle($this->mch, $this->chs[$this->rid]);
    }
    
    public function recv()
    {
        if ($this->restarted) {
            $this->restarted = false;
            
            // fool xmpp_stream state machine with stream start packet
            // and make transition to wait_for_stream_features state
            if ($this->recv_cb) {
                call_user_func($this->recv_cb, $this->jaxl->get_start_stream(new XMPPJid("bosh.jaxl")));
            }
        }
        
        JAXLLogger::debug("recving for $this->rid");
        do {
            $mrc = curl_multi_exec($this->mch, $running);
            JAXLLogger::debug("mrc=$mrc running=$running");
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($running && $mrc == CURLM_OK) {
            $ms = curl_multi_select($this->mch, 0.1);
            if ($ms != -1) {
                do {
                    $mrc = curl_multi_exec($this->mch, $running);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        
        $ch = isset($this->chs[$this->rid]) ? $this->chs[$this->rid] : false;
        if ($ch) {
            $data = curl_multi_getcontent($ch);
            
            curl_multi_remove_handle($this->mch, $ch);
            unset($this->chs[$this->rid]);
            JAXLLogger::debug("recvd for $this->rid ".$data);
        
            list($body, $stanza) = $this->unwrap($data);
            $body = new SimpleXMLElement($body);
            $attrs = $body->attributes();
            
            if (isset($attrs['type']) && $attrs['type'] == 'terminate') {
                // fool me again
                if ($this->recv_cb) {
                    call_user_func($this->recv_cb, $this->jaxl->get_end_stream());
                }
            } else {
                if (!$this->sid) {
                    $this->sid = $attrs['sid'];
                }
                
                if ($this->recv_cb) {
                    call_user_func($this->recv_cb, $stanza);
                }
            }
        } else {
            JAXLLogger::error("no ch found");
            exit;
        }
    }

    /**
     * Emits on recv and session_start.
     *
     * @param callable $recv_cb
     */
    public function set_callback($recv_cb)
    {
        $this->recv_cb = $recv_cb;
    }
    
    public function wrap($stanza)
    {
        return '<body sid="'.$this->sid.'" rid="'.++$this->rid.'" xmlns="'.
            self::NS_HTTP_BIND.'">'.$stanza.'</body>';
    }
    
    public function unwrap($body)
    {
        // a dirty way but it works efficiently
        if (substr($body, -2, 2) == "/>") {
            preg_match_all('/<body (.*?)\/>/smi', $body, $m);
        } else {
            preg_match_all('/<body (.*?)>(.*)<\/body>/smi', $body, $m);
        }
        
        if (isset($m[1][0])) {
            $envelop = "<body ".$m[1][0]."/>";
        } else {
            $envelop = "<body/>";
        }
        
        if (isset($m[2][0])) {
            $payload = $m[2][0];
        } else {
            $payload = '';
        }
        
        return array($envelop, $payload);
    }
    
    public function session_start()
    {
        $this->rid = isset($this->jaxl->cfg['bosh_rid']) ? $this->jaxl->cfg['bosh_rid'] : rand(1000, 10000);
        $this->hold = isset($this->jaxl->cfg['bosh_hold']) ? $this->jaxl->cfg['bosh_hold'] : $this->hold;
        $this->wait = isset($this->jaxl->cfg['bosh_wait']) ? $this->jaxl->cfg['bosh_wait'] : $this->wait;
        
        // fool xmpp_stream state machine with stream start packet
        // and make transition to wait_for_stream_features state
        if ($this->recv_cb) {
            call_user_func($this->recv_cb, $this->jaxl->get_start_stream(new XMPPJid("bosh.jaxl")));
        }
        
        $attrs = array(
            'content' => 'text/xml; charset=utf-8',
            'to' => (($this->jaxl && $this->jaxl->jid)
                     ? $this->jaxl->jid->domain
                     : $this->jaxl->cfg['domain']),
            'route' => 'xmpp:'.$this->jaxl->cfg['host'].':'.$this->jaxl->cfg['port'],
            'secure' => 'true',
            'xml:lang' => 'en',
            'xmpp:version' => '1.0',
            'xmlns:xmpp' => self::NS_BOSH,
            'hold' => $this->hold,
            'wait' => $this->wait,
            'rid' => $this->rid,
            'ver' => '1.10'
        );
        
        if ($this->jaxl->cfg['jid'] !== null) {
            $attrs['from'] = $this->jaxl->cfg['jid'];
        }
        $body = new JAXLXml('body', self::NS_HTTP_BIND, $attrs);
        $this->send($body);
    }
    
    public function ping()
    {
        $body = new JAXLXml('body', self::NS_HTTP_BIND, array('sid' => $this->sid, 'rid' => ++$this->rid));
        $this->send($body);
    }
    
    public function session_end()
    {
        $this->disconnect();
    }
    
    public function disconnect()
    {
        JAXLLogger::debug("disconnecting");
    }
}
