<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Label\CreateLabelRequest;
use App\Http\Requests\Product\Label\UpdateLabelRequest;
use App\Http\Resources\LabelResource;
use App\Http\Traits\HasCompanyScope;
use App\Models\Label;
use Illuminate\Support\Facades\Auth;

class LabelsController extends Controller
{
    use HasCompanyScope;
    
    public function index()
    {
        return response()->json(LabelResource::collection(Label::all()));
    }

    public function show(Label $label)
    {
        return response()->json(new LabelResource($label));
    }

    public function store(CreateLabelRequest $request)
    {        
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can create labels.'], 403);
        }
                
        $label = Label::create($request->validatedWithCompany());
        
        return response()->json(new LabelResource($label), 201);
    }

    public function update(UpdateLabelRequest $request, Label $label)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can update labels.'], 403);
        }
        $label->update($request->all());
        return response()->json(new LabelResource($label));
    }

    public function destroy(Label $label)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can delete labels.'], 403);
        }
        return $label->delete();
    }
}
