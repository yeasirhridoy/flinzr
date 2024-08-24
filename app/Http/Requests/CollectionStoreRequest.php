<?php

namespace App\Http\Requests;

use App\Enums\PlatformType;
use App\Enums\SalesType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollectionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'type' => ['required', Rule::enum(PlatformType::class)],
            'eng_name' => 'required|string|max:255',
            'eng_description' => 'required|string|max:255',
            'arabic_name' => 'required|string|max:255',
            'arabic_description' => 'required|string|max:255',
            'sales_type' => ['required', Rule::enum(SalesType::class)],
            'avatar' => 'required',
            'thumbnail' => 'required',
            'cover' => 'required',
            'filters' => 'required|array|min:1|max:8',
            'filters.*.name' => 'required|string|max:255',
            'filters.*.image' => 'required|string|max:255',
        ];
    }
}
