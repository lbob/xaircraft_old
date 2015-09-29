<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/9/29
 * Time: 11:01
 */

namespace Xaircraft\Plugins\WeChat\API\Contract;


use Xaircraft\Exception\ExceptionHelper;
use Xaircraft\Mvc\BaseModel;

class NewsArticle extends BaseModel
{

    public $title;
    public $description;
    public $url;
    public $picurl;

    public function validate()
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($this->title, "title ²»ÄÜÎª¿Õ");
    }
}