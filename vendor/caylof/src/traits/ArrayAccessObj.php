<?php
namespace caylof\traits;


/**
 * 同时实现ArrayAccess和Countable以及Iterator三个接口的trait
 */
trait ArrayAccessObj {

    private $data = [];

    // 实现ArrayAccess接口
    public function &__get ($key) {
        return $this->data[$key];
    }
    public function __set($key,$value) {
        $this->data[$key] = $value;
    }
    public function __isset ($key) {
        return isset($this->data[$key]);
    }
    public function __unset($key) {
        unset($this->data[$key]);
    }
    public function offsetSet($offset,$value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    // 实现Countable接口
    public function count() {
        return \count($this->data);
    }

    // 实现Iterator接口
    public function rewind() {
        return \reset($this->data);
    }
    public function current() {
        return \current($this->data);
    }
    public function key() {
        return \key($this->data);
    }
    public function next() {
        return \next($this->data);
    }
    public function valid() {
        return \key($this->data) !== null;
    }
}
