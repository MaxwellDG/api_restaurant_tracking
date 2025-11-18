<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $company = Company::create([
            'name' => $request->name,
            'user_id' => $user->id
        ]);

        // Update the user's company_id to the newly created company
        $user->createCompany($company);

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

    public function join(Request $request, Company $company)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user is already in a company
        if ($user->company_id) {
            return response()->json([
                'message' => 'You are already a member of a company.'
            ], 400);
        }

        // Join the company
        $user->joinCompany($company);

        return response()->json([
            'message' => 'Successfully joined company.',
            'company' => new CompanyResource($company)
        ]);
    }
}
