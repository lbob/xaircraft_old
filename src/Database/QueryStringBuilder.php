<?php

namespace Xaircraft\Database;


/**
 * Class QueryStringBuilder
 *
 * @package Xaircraft\Database
 * @author skyweo created at 2015/7/14 1:58
 */
interface QueryStringBuilder {

    /**
     * @return mixed
     */
    public function getQueryString();

    /**
     * @return array
     */
    public function getQueryParameters();
}

