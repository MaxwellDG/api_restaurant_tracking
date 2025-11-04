<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

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

        return response()->json($company);
    }

    public function show(Company $company)
    {
        return response()->json($company);
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
        return response()->json($company);
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
