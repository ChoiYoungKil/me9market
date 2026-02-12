<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index()
    {
        return redirect()->route('admin.dashboard');
    }

    public function sub01()
    {
        return redirect()->route('admin.sub01');
    }

    public function sub02()
    {
        return redirect()->route('admin.sub02');
    }

    public function sub03()
    {
        return redirect()->route('admin.sub03');
    }

    public function loading()
    {
        return redirect()->route('admin.loading');
    }
}
