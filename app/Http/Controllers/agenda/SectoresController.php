<?php

namespace App\Http\Controllers\agenda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\agenda\Sectores;

class SectoresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       /*  $sectores = Sectores::all();
        return response()->json($sectores); */
        /* return view('clientes.gestionasesores.asesores'); */
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
    public function store(Request $request)
    {
        $sector = Sectores::create($request->all());
        return response()->json($sector, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $sector = Sectores::findOrFail($id);
        return response()->json($sector);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sector = Sectores::findOrFail($id);
        $sector->update($request->all());
        return response()->json($sector);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $sector = Sectores::findOrFail($id);
        $sector->delete();
        return response()->json(null, 204);
    }
}
