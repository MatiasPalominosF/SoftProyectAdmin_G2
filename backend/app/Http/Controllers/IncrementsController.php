<?php

namespace App\Http\Controllers;

use App\Increment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IncrementsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $increments = Increment::where('project_id', $request->project_id)->get();
        return response()->json($increments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'initDate' => 'required',
            'endDate' => 'required',
            'project_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $increment = new Increment();
        $increment->name = 'Incremento '.Increment::where('project_id', $request->project_id)->count();
        $increment->description = $request->description;
        $initDate = $request->initDate['year'].'-'.$request->initDate['month'].'-'.$request->initDate['day'];
        $increment->initDate = date('Y-m-d', strtotime($initDate));
        $endDate = $request->endDate['year'].'-'.$request->endDate['month'].'-'.$request->endDate['day'];
        $increment->endDate = date('Y-m-d', strtotime($endDate));
        $increment->project_id = $request->project_id;
        $increment->save();

        return response()->json([
            'success' => true,
            'msg' => 'El incremento fue agregado exitosamente.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $increments = Increment::find($id);
        return response()->json($increments);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'initDate' => 'required',
            'endDate' => 'required',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $increment = Increment::find($id);
        $increment->name = $request->name;
        $increment->description = $request->description;
        $increment->initDate = $request->initDate;
        $increment->endDate = $request->endDate;
        $increment->project_id = $request->project_id;
        $increment->save();

        return response()->json([
            'success' => true,
            'msg' => 'El incremento fue modificado exitosamente.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $increment = Increment::find($id);
        $increment->delete();
    }
}