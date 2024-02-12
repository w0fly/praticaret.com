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
 *
 * Enter description here ...
 * @author abhinavsingh
 *
 */
class JAXLSocketClient implements JAXLClientBase
{

    private $host = null;
    private $port = null;
    private $transport = null;
    /** @var resource */
    private $stream_context = null;
    private $blocking = false;

    public $fd = null;

    public $errno = null;
    public $errstr = null;
    private $timeout = 10;

    private $ibuffer = "";
    private $obuffer = "";
    private $compressed = false;

    private $recv_bytes = 0;
    private $send_bytes = 0;

    /** @var callable */
    private $recv_cb = null;
    private $recv_chunk_size = 1024;
    private $writing = false;

    /**
     * @param resource $stream_context  Resource created with stream_context_create().
     */
    public function __construct($stream_context = null)
    {
        $this->stream_context = $stream_context;
    }

    public function __destruct()
    {
        //JAXLLogger::debug("cleaning up xmpp socket...");
        $this->disconnect();
    }

    /**
     * Emits on on_read_ready.
     *
     * @param callable $recv_cb
     */
    public function set_callback($recv_cb)
    {
        $this->recv_cb = $recv_cb;
    }

    /**
     * @param string | resource $socket_path
     */
    public function connect($socket_path)
    {
        if (gettype($socket_path) == "string") {
            $path_parts = explode(":", $socket_path);
            $this->transport =  $path_parts[0];
            $this->host = substr($path_parts[1], 2, strlen($path_parts[1]));
            if (count($path_parts) == 3) {
                $this->port = $path_parts[2];
            }

            JAXLLogger::info("trying ".$socket_path);
            if ($this->stream_context) {
                $this->fd = @stream_socket_client(
                    $socket_path,
                    $this->errno,
                    $this->errstr,
                    $this->timeout,
                    STREAM_CLIENT_CONNECT,
                    $this->stream_context
                );
            } else {
                $this->fd = @stream_socket_client($socket_path, $this->errno, $this->errstr, $this->timeout);
            }
        } elseif (is_resource($socket_path)) {
            $this->fd = &$socket_path;
        } else {
            // Some error occurred.
        }

        if ($this->fd) {
            JAXLLogger::debug("connected to ".$socket_path."");
            stream_set_blocking($this->fd, $this->blocking);

            // watch descriptor for read/write events
            JAXLLoop::watch($this->fd, array(
                'read' => array(&$this, 'on_read_ready')
            ));

            return true;
        } else {
            JAXLLogger::error(sprintf(
                "unable to connect %s with error no: %s, error str: %s",
                is_null($socket_path) ? 'NULL' : $socket_path,
                is_null($this->errno) ? 'NULL' : $this->errno,
                is_null($this->errstr) ? 'NULL' : $this->errstr
            ));
            $this->disconnect();
            return false;
        }
    }

    public function disconnect()
    {
        JAXLLoop::unwatch($this->fd, array(
            'read' => true,
            'write' => true
        ));
        if (is_resource($this->fd)) {
            fclose($this->fd);
        }
        $this->fd = null;
    }

    public function compress()
    {
        $this->compressed = true;
        //stream_filter_append($this->fd, 'zlib.inflate', STREAM_FILTER_READ);
        //stream_filter_append($this->fd, 'zlib.deflate', STREAM_FILTER_WRITE);
    }

    public function crypt()
    {
        // set blocking (since tls negotiation fails if stream is non-blocking)
        stream_set_blocking($this->fd, true);

        $ret = stream_socket_enable_crypto($this->fd, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if ($ret == false) {
            $ret = stream_socket_enable_crypto($this->fd, true, STREAM_CRYPTO_METHOD_SSLv3_CLIENT);
            if ($ret == false) {
                $ret = stream_socket_enable_crypto($this->fd, true, STREAM_CRYPTO_METHOD_SSLv2_CLIENT);
                if ($ret == false) {
                    $ret = stream_socket_enable_crypto($this->fd, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
                }
            }
        }

        // switch back to non-blocking
        stream_set_blocking($this->fd, false);
        return $ret;
    }

    /**
     * @param string $data
     */
    public function send($data)
    {
        $this->obuffer .= $data;

        // add watch for write events
        if ($this->writing) {
            return;
        }
        JAXLLoop::watch($this->fd, array(
            'write' => array(&$this, 'on_write_ready')
        ));
        $this->writing = true;
    }

    public function on_read_ready($fd)
    {
        //JAXLLogger::debug("on read ready called");
        $raw = @fread($fd, $this->recv_chunk_size);
        $bytes = strlen($raw);

        if ($bytes === 0) {
            $meta = stream_get_meta_data($fd);
            if ($meta['eof'] === true) {
                JAXLLogger::warning("socket eof, disconnecting");
                JAXLLoop::unwatch($fd, array(
                    'read' => true
                ));
                $this->disconnect();
                return;
            }
        }

        $this->recv_bytes += $bytes;
        $total = $this->ibuffer.$raw;

        $this->ibuffer = "";
        JAXLLogger::debug("read ".$bytes."/".$this->recv_bytes." of data");
        if ($bytes > 0) {
            JAXLLogger::debug($raw);
        }

        // callback
        if ($this->recv_cb) {
            call_user_func($this->recv_cb, $raw);
        }
    }

    public function on_write_ready($fd)
    {
        //JAXLLogger::debug("on write ready called");
        $total = strlen($this->obuffer);
        $bytes = @fwrite($fd, $this->obuffer);
        $this->send_bytes += $bytes;

        JAXLLogger::debug("sent ".$bytes."/".$this->send_bytes." of data");
        JAXLLogger::debug(substr($this->obuffer, 0, $bytes));

        $this->obuffer = substr($this->obuffer, $bytes, $total-$bytes);

        // unwatch for write if obuffer is empty
        if (strlen($this->obuffer) === 0) {
            JAXLLoop::unwatch($fd, array(
                'write' => true
            ));
            $this->writing = false;
        }

        //JAXLLogger::debug("current obuffer size: ".strlen($this->obuffer)."");
    }
}
