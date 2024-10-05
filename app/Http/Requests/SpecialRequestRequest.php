<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpecialRequestRequest extends FormRequest
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
            'category_id' => ['required', 'exists:categories,id'],
            'platform' => ['required', 'string','in:instagram,tiktok,snapchat,banner'],
            'occasion' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if (preg_match('/^data:image\/(\w+);base64,/', $value, $type)) {
                    $data = substr($value, strpos($value, ',') + 1);
                    $data = base64_decode($data);

                    if ($data === false) {
                        $fail('The '.$attribute.' must be a valid base64 encoded image.');
                    }

                    $allowedMimeTypes = ['jpeg', 'jpg', 'png'];
                    if (!in_array(strtolower($type[1]), $allowedMimeTypes)) {
                        $fail('The '.$attribute.' must be a file of type: jpeg, jpg, png.');
                    }
                } else {
                    $fail('The '.$attribute.' must be a valid base64 encoded image.');
                }
            }],
        ];
    }
}
