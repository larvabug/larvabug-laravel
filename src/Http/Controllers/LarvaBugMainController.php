<?php


namespace LarvaBug\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class LarvaBugMainController extends BaseController
{

    /**
     * Post exception collected feedback to larvabug
     *
     * @return mixed
     */
    public function postExceptionFeedback()
    {
        $data = Request::only('name','email','message','exceptionId');

        app('larvabug')->submitFeedback($data);

        return Redirect::to('/');
    }

    /**
     * Collect feedback view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function postFeedback()
    {
        return view('larvabug::feedback');
    }

}