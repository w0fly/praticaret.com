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
 * Following kind of events are possible:
 * 1) hook i.e. if a callback for such an event is registered, calling function
 *    is responsible for the workflow from their on
 * 2) filter i.e. calling function will manipulate passed arguments
 *    and modified arguments will be passed to next chain of filter
 *
 * As a rule of thumb, only 1 hook can be registered for an event, while more
 * than 1 filter is allowed for an event hook and filter both cannot be applied
 * on an event.
 *
 * @author abhinavsingh
 *
 */
class JAXLEvent
{

    protected $common = array();
    protected $reg = array();

    /**
     * @param array $common
     */
    public function __construct(array $common = array())
    {
        $this->common = $common;
    }

    public function __destruct()
    {
    }

    /**
     * Add callback on a event.
     *
     * Callback'd method must return `true` to be persistent, otherwise
     * if returned `null` or `false`, callback will be removed automatically.
     *
     * @param string $ev
     * @param callable $cb
     * @param int $priority
     * @return string Reference to be used while deleting callback.
     */
    public function add($ev, $cb, $priority)
    {
        if (!isset($this->reg[$ev])) {
            $this->reg[$ev] = array();
        }

        $ref = count($this->reg[$ev]);
        $this->reg[$ev][] = array($priority, $cb);
        return $ev."-".$ref;
    }

    /**
     * Emit event to notify registered callbacks.
     *
     * TODO: Is a pqueue required here for performance enhancement in case we
     * have too many cbs on a specific event?
     *
     * @param string $ev
     * @param array $data
     * @return array
     */
    public function emit($ev, array $data = array())
    {
        $data = array_merge($this->common, $data);
        $cbs = array();

        if (!isset($this->reg[$ev])) {
            return $data;
        }

        foreach ($this->reg[$ev] as $cb) {
            if (!isset($cbs[$cb[0]])) {
                $cbs[$cb[0]] = array();
            }
            $cbs[$cb[0]][] = $cb[1];
        }

        foreach ($cbs as $pri => $cb) {
            foreach ($cb as $c) {
                $ret = call_user_func_array($c, $data);
                // This line is for fixing situation where callback function doesn't return an array type.
                // In such cases next call of call_user_func_array will report error since $data is not
                // an array type as expected.
                // Things will change in future, atleast put the callback inside a try/catch block.
                // Here we only check if there was a return, if yes we update $data with return value.
                // This is bad design, need more thoughts, should work as of now.
                if ($ret) {
                    $data = $ret;
                }
            }
        }

        unset($cbs);
        return $data;
    }

    /**
     * Remove previous registered callback.
     *
     * @param string $ref
     */
    public function del($ref)
    {
        $ref = explode("-", $ref);
        unset($this->reg[$ref[0]][$ref[1]]);
    }

    /**
     * @param string $ev
     * @return bool
     */
    public function exists($ev)
    {
        $ret = isset($this->reg[$ev]);
        //JAXLLogger::debug("event ".$ev." callback ".($ret ? "exists" : "do not exists"));
        return $ret;
    }

    /**
     * @return array List of registered events.
     */
    public function getRegistry()
    {
        return $this->reg;
    }
}
