<?php

namespace App\Http\Controllers\agenda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\agenda\notas;

class NotasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notas = notas::all();
        return response()->json($notas);
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
        $nota = notas::create($request->all());
        return response()->json($nota, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $nota = notas::findOrFail($id);
        return response()->json($nota);
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
        $nota = notas::findOrFail($id);
        $nota->update($request->all());
        return response()->json($nota);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $nota = notas::findOrFail($id);
        $nota->delete();
        return response()->json(null, 204);
    }
}
