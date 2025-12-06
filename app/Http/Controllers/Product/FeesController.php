<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Fees\CreateFeeRequest;
use App\Http\Requests\Product\Fees\UpdateFeeRequest;
use App\Http\Resources\FeeResource;
use App\Http\Traits\HasCompanyScope;
use App\Models\Fee;
use Illuminate\Support\Facades\Auth;

class FeesController extends Controller
{
    use HasCompanyScope;

    /**
     * Display a listing of fees for the authenticated user's company.
     */
    public function index()
    {
        $companyId = $this->getAuthCompanyId();
        $fees = Fee::where('company_id', $companyId)->get();
        
        return response()->json(FeeResource::collection($fees));
    }

    /**
     * Store a newly created fee in storage.
     */
    public function store(CreateFeeRequest $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can create fees.'], 403);
        }
        
        $fee = Fee::create($request->validatedWithCompany());
        
        return response()->json(new FeeResource($fee), 201);
    }

    /**
     * Display the specified fee.
     */
    public function show(Fee $fee)
    {
        // Ensure the fee belongs to the user's company
        if ($fee->company_id !== $this->getAuthCompanyId()) {
            return response()->json(['error' => 'Unauthorized. This fee does not belong to your company.'], 403);
        }
        
        return response()->json(new FeeResource($fee));
    }

    /**
     * Update the specified fee in storage.
     */
    public function update(UpdateFeeRequest $request, Fee $fee)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can update fees.'], 403);
        }
        
        // Ensure the fee belongs to the user's company
        if ($fee->company_id !== $this->getAuthCompanyId()) {
            return response()->json(['error' => 'Unauthorized. This fee does not belong to your company.'], 403);
        }
        
        $fee->update($request->validated());
        
        return response()->json(new FeeResource($fee));
    }

    /**
     * Remove the specified fee from storage.
     */
    public function destroy(Fee $fee)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can delete fees.'], 403);
        }
        
        // Ensure the fee belongs to the user's company
        if ($fee->company_id !== $this->getAuthCompanyId()) {
            return response()->json(['error' => 'Unauthorized. This fee does not belong to your company.'], 403);
        }
        
        $fee->delete();
        
        return response()->json(['message' => 'Fee deleted successfully']);
    }
}
