<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'email' => $this->email,
            'tel' => $this->tel,
            'address' => $this->address,
            'building' => $this->building,
            'detail' => $this->detail,
            'category' => $this->whenLoaded('category',function(){
                return[
                    'id' => $this->category->id,
                    'content' => $this->category->content,
                ];
            }),
            'tags' =>$this->whenLoaded('tags',function(){
                return $this->tags->map(fn($tag)=>[
                    'id' => $tag->id,
                    'name' => $tag->name,
                ]);
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
