<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray($request)
    {
        $isCompanyMember = $request->user() && 
                          $request->user()->company_id === $this->resource->id;

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            $this->mergeWhen($isCompanyMember, [
                'members' => UserResource::collection(User::where('company_id', $this->resource->id)->get()),
            ]),
        ];
    }

    
}