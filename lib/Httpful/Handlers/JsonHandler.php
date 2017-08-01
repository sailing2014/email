<?php

/**
 * Mime Type: application/json
 * @author Nathan Good <me@nategood.com>
 */

namespace Httpful\Handlers;

class JsonHandler extends MimeHandlerAdapter {

    private $decode_as_array = false;

    public function init(array $args) {
        $this->decode_as_array = !!(array_key_exists('decode_as_array', $args) ? $args['decode_as_array'] : false);
    }

    /**
     * @param string $body
     * @return mixed
     */
    public function parse($body) {
        $body = $this->stripBom($body);
        if (empty($body))
            return null;
        $parsed = json_decode($body, $this->decode_as_array);
        if (is_null($parsed) && 'null' !== strtolower($body))
            throw new \Exception("Unable to parse response as JSON");
        return $parsed;
    }

    /**
     * @param mixed $payload
     * @return string
     */
    public function serialize($payload) {
        return json_encode($payload);
    }

    protected function stripBom($body) {
        if (substr($body, 0, 3) === "\xef\xbb\xbf")  // UTF-8
            $body = substr($body, 3);
        else if (substr($body, 0, 4) === "\xff\xfe\x00\x00" || substr($body, 0, 4) === "\x00\x00\xfe\xff")  // UTF-32
            $body = substr($body, 4);
        else if (substr($body, 0, 2) === "\xff\xfe" || substr($body, 0, 2) === "\xfe\xff")  // UTF-16
            $body = substr($body, 2);
        return $body;
    }

}
