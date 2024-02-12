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

/**
 * Generic xmpp stanza object which provide convinient access pattern over xml objects
 * Also to be able to convert an existing xml object into stanza object (to get access patterns going)
 * this class doesn't extend xml, but infact works on a reference of xml object
 * If not provided during constructor, new xml object is created and saved as reference
 *
 * @author abhinavsingh
 *
 * Access to common xml attributes:
 *
 * @property string $to
 * @property string $from
 * @property string $id
 * @property string $type
 *
 * Access to parts of common xml attributes:
 *
 * @property string $to_node
 * @property string $to_domain
 * @property string $to_resource
 * @property string $from_node
 * @property string $from_domain
 * @property string $from_resource
 *
 * Access to first child element text:
 *
 * @property string $status
 * @property string $show
 * @property string $priority
 * @property string $body
 * @property string $thread
 * @property string $subject
 */
class XMPPStanza extends JAXLXmlAccess
{

    /**
     * @var JAXLXml
     */
    private $xml;

    /**
     * @param JAXLXml|string $name
     * @param array $attrs
     * @param string $ns
     */
    public function __construct($name, array $attrs = array(), $ns = XMPP::NS_JABBER_CLIENT)
    {
        // TRICKY: Remove JAXLXmlAccess properties, so magic method __get will
        // be called for them. This needed to use JAXLXmlAccess as a type hint.
        $this->name = null;
        unset($this->name);
        $this->ns = null;
        unset($this->ns);
        $this->attrs = null;
        unset($this->attrs);
        $this->text = null;
        unset($this->text);
        $this->children = null;
        unset($this->children);

        if ($name instanceof JAXLXml) {
            $this->xml = $name;
        } else {
            $this->xml = new JAXLXml($name, $ns, $attrs);
        }
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->xml, $method), $args);
    }

    public function __get($prop)
    {
        switch ($prop) {
            // access to jaxl xml properties
            case 'name':
            case 'ns':
            case 'text':
            case 'attrs':
            case 'children':
                return $this->xml->$prop;
                break;

            // access to common xml attributes
            case 'to':
            case 'from':
            case 'id':
            case 'type':
                return isset($this->xml->attrs[$prop]) ? $this->xml->attrs[$prop] : null;
                break;

            // access to parts of common xml attributes
            case 'to_node':
            case 'to_domain':
            case 'to_resource':
            case 'from_node':
            case 'from_domain':
            case 'from_resource':
                list($attr, $key) = explode('_', $prop);
                $val = isset($this->xml->attrs[$attr]) ? $this->xml->attrs[$attr] : null;
                if (!$val) {
                    return null;
                }

                $val = new XMPPJid($val);
                return $val->$key;
                break;

            // access to first child element text
            case 'status':
            case 'show':
            case 'priority':
            case 'body':
            case 'thread':
            case 'subject':
                $val = $this->xml->exists($prop);
                if (!$val) {
                    return null;
                }
                return $val->text;
                break;

            default:
                return null;
                break;
        }
    }

    public function __set($prop, $val)
    {
        switch ($prop) {
            // access to jaxl xml properties
            case 'name':
            case 'ns':
            case 'text':
            case 'attrs':
            case 'children':
                return $this->xml->$prop = $val;
                break;

            // access to common xml attributes
            case 'to':
            case 'from':
            case 'id':
            case 'type':
                $this->xml->attrs[$prop] = $val;
                return true;
                break;

            // access to parts of common xml attributes
            case 'to_node':
            case 'to_domain':
            case 'to_resource':
            case 'from_node':
            case 'from_domain':
            case 'from_resource':
                list($attr, $key) = explode('_', $prop);
                $val1 = isset($this->xml->attrs[$attr]) ? $this->xml->attrs[$attr] : null;
                if (!$val1) {
                    $val1 = '';
                }

                $val1 = new XMPPJid($val1);
                $val1->$key = $val;

                $this->xml->attrs[$attr] = $val1->to_string();
                return true;
                break;

            // access to first child element text
            case 'status':
            case 'show':
            case 'priority':
            case 'body':
            case 'thread':
            case 'subject':
                $val1 = $this->xml->exists($prop);
                if (!$val1) {
                    $this->xml->c($prop)->t($val)->up();
                } else {
                    $this->xml->update($prop, $val1->ns, $val1->attrs, $val);
                }
                return true;
                break;

            default:
                return null;
                break;
        }
    }
}
