<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    /**
     * Show privacy policy page
     */
    public function privacyPolicy()
    {
        return view('public.privacy-policy');
    }

    /**
     * Show terms and conditions page
     */
    public function termsConditions()
    {
        return view('public.terms-conditions');
    }

    /**
     * Show about us page
     */
    public function aboutUs()
    {
        return view('public.about-us');
    }
}
