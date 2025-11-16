<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $company = Company::create([
            'name' => $request->name,
            'user_id' => $request->user()->id,
        ]);

        return new CompanyResource($company);
    }

    public function show(Company $company)
    {        
        return new CompanyResource($company);
    }

    public function update(Request $request, Company $company)
    {
        // Check if the authenticated user is the creator of the company
        if ($company->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to update this company.'
            ], 403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $company->update($request->all());
        return new CompanyResource($company);
    }

    public function destroy(Request $request, Company $company)
    {
        // Check if the authenticated user is the creator of the company
        if ($company->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to delete this company.'
            ], 403);
        }

        $company->delete();
        return response()->json(['message' => 'Company deleted successfully']);
    }
}
