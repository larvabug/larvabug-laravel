<?php


namespace LarvaBug\Facade;


use Illuminate\Support\Facades\Facade as FacadeAlias;

class LarvaBug extends FacadeAlias
{
    protected static function getFacadeAccessor()
    {
        return 'larvabug';
    }
}