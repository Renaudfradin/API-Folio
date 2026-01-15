<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorecameraRequest;
use App\Http\Requests\UpdatecameraRequest;
use App\Models\camera;

class CameraController extends Controller
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
    public function store(StorecameraRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(camera $camera)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(camera $camera)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatecameraRequest $request, camera $camera)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(camera $camera)
    {
        //
    }
}
