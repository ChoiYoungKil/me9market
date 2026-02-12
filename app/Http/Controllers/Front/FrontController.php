<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index()
    {
        return view('front.index');
    }

    public function notice()
    {
        return view('front.pages.notice');
    }

    public function noticeView()
    {
        return view('front.pages.notice_view');
    }

    public function faq()
    {
        return view('front.pages.faq');
    }

    public function contact()
    {
        return view('front.pages.contact');
    }
}
