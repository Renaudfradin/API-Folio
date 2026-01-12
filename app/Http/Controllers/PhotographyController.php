<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhotographyRequest;
use App\Http\Requests\UpdatePhotographyRequest;
use App\Models\Photography;

class PhotographyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePhotographyRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Photography $photography)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Photography $photography)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePhotographyRequest $request, Photography $photography)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Photography $photography)
    {
        //
    }
}
